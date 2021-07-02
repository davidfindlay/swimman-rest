<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\DisabilityClassification;
use App\MeetAccess;
use App\MeetEntry;
use App\MeetEntryEmails;
use App\MeetEvent;
use App\EventDiscipline;
use App\EventDistance;
use App\MeetEntryEvent;
use App\MeetEntryOrder;
use App\MeetEntryPayment;
use App\MeetEntryStatus;
use App\MeetEntryIncomplete;
use App\MeetEntryStatusCode;
use App\MeetEntryOrderItem;
use App\MeetMerchandise;
use App\Member;
use App\Meet;
use App\Club;
use App\AgeGroup;

use App\PayPalPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use mysql_xdevapi\Exception;
use Psr\Log\NullLogger;

use DateTime;

class MeetEntryController extends Controller {

    private $request;
    private $userId;
    private $user;

    /**
     * MemberController constructor.
     */
    public function __construct(Request $request) {
        $this->request = $request;
        $user = $this->request->user();
        if ($user != NULL) {
            $this->user = $user;
            $this->userId = intval($user->id);
        } else {
            $this->userId = NULL;
        }
    }

    public function createIncompleteEntry() {

//        if ($this->userId != NULL && $this->userId != intval($this->request->all()['user_id'])) {
//            return response()->json(['error' => "You cannot submit an entry for another user!"],403);
//        }

        $entry = $this->request->all();
//        if ($entry->entrydata != NULL) {
//            $meetId = $entry->entrydata->meetId;
//            if ($meetId != NULL) {
//                $entry['meet_id'] = $meetId;
//            }
//        }

        if ($this->userId != NULL) {
            $entry['user_id'] = $this->userId;

            if ($this->request->user()->member != NULL) {
                $entry['member_id'] = $this->request->user()->member;
            }
        }

        $entryData = json_encode($entry['entrydata']);
        $entry['entrydata'] = $entryData;

        $statusCode = MeetEntryStatusCode::where('label', '=', 'Incomplete')->first();
        $entry['status_id'] = $statusCode->id;
        $entry['status_label'] = $statusCode->label;
        $entry['status_description'] = $statusCode->description;

        // Generate an entry code for access to this entry
        $uniqueCode = false;
        while (!$uniqueCode) {
            $entry['code'] = $this->random_str(8);
            $duplicateCheck = MeetEntryIncomplete::where('code', '=', $entry['code'])->get();
            if (count($duplicateCheck) == 0) {
                $uniqueCode = true;
            }
            // $uniqueCode = true;
        }

        $entryObj = MeetEntryIncomplete::create($entry);
        $entryObj['entrydata'] = json_decode($entry['entrydata'], true);
        return response()->json($entryObj);
    }

    function random_str(
        $length = 64,
        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyz'
    ) {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        try {
            for ($i = 0; $i < $length; ++$i) {
                $pieces [] = $keyspace[random_int(0, $max)];
            }
        } catch (Exception $e) {
            return null;
        }
        return implode('', $pieces);
    }

    public function updateIncompleteEntry($code) {
        $entry = MeetEntryIncomplete::where('code', '=', $code)->first();

        if ($entry == NULL) {
            return response()->json('Unable to remove incomplete entry.', 404);
        }

        if ($entry->user_id != NULL && !$this->isAdmin()) {
            if ($this->userId != intval($entry->user_id)) {
                return response()->json(['error' => "You cannot edit an entry for another user "], 403);
            }
        }

        $entry->entrydata = json_encode($this->request->input('entrydata'));
        $entry->save();
        $entry['entrydata'] = json_decode($entry['entrydata'], true);
        $statusCode = MeetEntryStatusCode::find($entry->status_id);
        $entry['status_label'] = $statusCode->label;
        $entry['status_description'] = $statusCode->description;

        return response()->json($entry);
    }

    private function isAdmin() {
        if ($this->user) {
            if ($this->user->is_admin) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function deleteIncompleteEntry($code) {
        $entry = MeetEntryIncomplete::where('code', '=', $code)->first();

        if ($entry == NULL) {
            return response()->json('Unable to remove incomplete entry.', 404);
        }

        if ($entry->user_id != NULL && !$this->user->is_admin) {
            if ($this->userId != intval($entry->user_id)) {
                return response()->json(['error' => "You cannot edit an entry for another user "], 403);
            }
        }

        $cancelledStatus = MeetEntryStatusCode::where('label', '=', 'Cancelled')->first();
        $entry->status_id = $cancelledStatus->id;
        $entry->saveOrFail();

        return response()->json('Removed successfully.');
    }

    public function getIncompleteEntry($code) {
        $entry = MeetEntryIncomplete::where('code', '=', $code)->first();

        if ($entry == NULL) {
            return response()->json('Unable to get incomplete entry.', 404);
        }

        if ($entry->user_id != NULL && $this->user != NULL && !$this->user->is_admin) {
            if ($this->userId != intval($entry->user_id)) {
                return response()->json(['error' => "You cannot get an entry for another user "], 403);
            }
        }

        $entry->entrydata = json_decode($entry->entrydata, true);

        $statusCode = MeetEntryStatusCode::find($entry->status_id);
        $entry['status_label'] = $statusCode->label;
        $entry['status_description'] = $statusCode->description;

        return response()->json($entry);
    }

    public function index() {
        $entry = MeetEntryIncomplete::where('user_id', '=', $this->userId)
            ->whereNull('finalised_at')->get();
        $outputEntries = array();
        foreach ($entry as $e) {
            $e->entrydata = json_decode($e->entrydata);
            $e['status'] = $e->status;
            if (!$e->status->cancelled) {
                array_push($outputEntries, $e);
            }
        }
        return response()->json($outputEntries);
    }

    public function finaliseIncompleteEntry($code) {
        $entry = MeetEntryIncomplete::where('code', '=', $code)->first();
        $status = 0;

        // If user is not logged in finalise to pending
//        if ($this->userId == null) {
//            $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
//            $entry->status_id = $pendingStatus->id;
//            $pendingReason = 'User not logged in, so entry set to pending.';
//            $entry->pending_reason = $pendingReason;
//            $entry->saveOrFail();
//            $entry->entrydata = json_decode($entry->entrydata, true);
//            return response()->json([
//                'pending_entry' => $entry,
//                'status_id' => $pendingStatus->id,
//                'explanation' => $pendingReason,
//                'status_label' => $pendingStatus->label,
//                'status_description' => $pendingStatus->description], 200);
//        }

        $entryData = json_decode($entry['entrydata']);

        if ($entryData->membershipDetails != null) {

            $entrantDetails = $entryData->entrantDetails;
            $membershipDetails = $entryData->membershipDetails;

            if (strval($membershipDetails->member_number) == '0') {
                $membershipDetails->member_number = '';
            }

            if (strval($membershipDetails->member_number) < 5) {
                $membershipDetails->member_number = '';
            }

            // If the Entrant DOB is the wrong format, fix it
            if (strpos($entrantDetails->entrantDob, '/') !== false) {
                $dateObj = DateTime::createFromFormat('d/m/Y', $entrantDetails->entrantDob);
                $entrantDetails->entrantDob = $dateObj->format('Y-m-d');
                \Sentry\captureMessage('entrantDetails->entrantDob was in Australian date format DD/MM/YYYY');

            }

            // User isn't an MSA member so don't try to create an entry for them
            if ($membershipDetails->member_type != 'msa') {
                $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
                $entry->status_id = $pendingStatus->id;
                $pendingReason = 'Logged in non-msa member, entry set to pending.';
                $entry->pending_reason = $pendingReason;
                $entry->saveOrFail();

                // Send confirmation email

                if (!property_exists($entryData, 'no_email') || !isset($entryData->no_email) || !$entryData->no_email) {

                    try {
                        $this->pendingEmail($entry);
                    } catch (Exception $e) {
                        \Sentry\captureMessage('Exception when sending pending email: ' . $e->getMessage());
                    }

                }
                $entry->entrydata = json_decode($entry->entrydata, true);

                return response()->json(['pending_entry' => $entry,
                    'status_id' => $pendingStatus->id,
                    'explanation' => $pendingReason,
                    'status_label' => $pendingStatus->label,
                    'status_description' => $pendingStatus->description], 200);
            }

            // Club id is set to other
            if ($membershipDetails->club_selector == 'other') {
                $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
                $entry->status_id = $pendingStatus->id;
                $pendingReason = 'Club ID is set to other';
                $entry->pending_reason = $pendingReason;
                $entry->saveOrFail();

                // Send confirmation email
                if (!property_exists($entryData, 'no_email') || !isset($entryData->no_email) || !$entryData->no_email) {
                    try {
                        $this->pendingEmail($entry);
                    } catch (Exception $e) {
                        \Sentry\captureMessage('Exception when sending pending email: ' . $e->getMessage());
                    }
                }

                $entry->entrydata = json_decode($entry->entrydata, true);

                return response()->json(['pending_entry' => $entry,
                    'status_id' => $pendingStatus->id,
                    'explanation' => $pendingReason,
                    'status_label' => $pendingStatus->label,
                    'status_description' => $pendingStatus->description], 200);
            }

            // If member number isn't set leave the entry as pending
            if ($membershipDetails->member_number == '') {
                $memberSearch = Member::where('surname', '=', trim($entrantDetails->entrantSurname))
                    ->where('firstname', '=', trim($entrantDetails->entrantFirstName))
                    ->where('dob', '=', $entrantDetails->entrantDob)
                    ->first();

                if ($memberSearch == NULL) {
                    //
                    $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
                    $entry->status_id = $pendingStatus->id;
                    $pendingReason = 'Member number not provided, member not found.';
                    $entry->pending_reason = $pendingReason;
                    $entry->saveOrFail();

                    // Send confirmation email
                    if (!property_exists($entryData, 'no_email') || !isset($entryData->no_email) || !$entryData->no_email) {
                        try {
                            $this->pendingEmail($entry);
                        } catch (Exception $e) {
                            \Sentry\captureMessage('Exception when sending pending email: ' . $e->getMessage());
                        }
                    }

                    $entry->entrydata = json_decode($entry->entrydata, true);

                    return response()->json(['pending_entry' => $entry,
                        'status_id' => $pendingStatus->id,
                        'explanation' => $pendingReason,
                        'status_label' => $pendingStatus->label,
                        'status_description' => $pendingStatus->description], 200);
                } else {
                    $membershipDetails->member_number = intval($memberSearch->number);
                    $entryData->membershipDetails = $membershipDetails;
                    $entry->entrydata = json_encode($entryData);
                }
            } else {
                $memberSearch = Member::where('number', '=', trim(strval($membershipDetails->member_number)))
                    ->where('surname', '=', trim($entrantDetails->entrantSurname))
                    ->where('dob', '=', $entrantDetails->entrantDob)
                    ->first();

                if (!isset($memberSearch)) {

                    // Membership number supplied does not match entrant details
                    $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
                    $entry->status_id = $pendingStatus->id;
                    $pendingReason = 'Member details supplied not found.';
                    $entry->pending_reason = $pendingReason;
                    $entry->saveOrFail();

                    // Send confirmation email
                    if (!property_exists($entryData, 'no_email') || !isset($entryData->no_email) || !$entryData->no_email) {
                        try {
                            $this->pendingEmail($entry);
                        } catch (Exception $e) {
                            \Sentry\captureMessage('Exception when sending pending email: ' . $e->getMessage());
                        }
                    }

                    $entry->entrydata = json_decode($entry->entrydata, true);
                    return response()->json(['pending_entry' => $entry,
                        'status_id' => $pendingStatus->id,
                        'explanation' => $pendingReason,
                        'status_label' => $pendingStatus->label,
                        'status_description' => $pendingStatus->description], 200);

                }
            }

            // Does Member already have entry for this event?
            $editMode = false;
            if (property_exists($entryData, 'edit_mode') && isset($entry->edit_mode)
                && $entry->edit_mode) {
                $editMode = true;
            }

            return $this->createOrUpdateEntry($entry, $editMode);

        } else {

            // set to pending
            $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
            $entry->status_id = $pendingStatus->id;
            $entry->saveOrFail();
            $entry->entrydata = json_decode($entry->entrydata, true);

        }

        return response()->json(['error' => 'unable to create entry', 'incomplete_entry' => $entry], 400);
    }

    private function createOrUpdateEntry($entry, $editMode) {

        $entryData = json_decode($entry['entrydata']);
//        return response()->json(['entrydata' => $entryData], 400);
        $membershipDetails = $entryData->membershipDetails;

        $member = Member::where('number', '=', $membershipDetails->member_number)->first();
        $ageUpDate = date('Y') . "-12-31";
        $age = date_diff(date_create($member->dob), date_create($ageUpDate))->format('%y');
        $gender = $member->gender;

        // If this is not an edit,
        if (!$editMode) {

            $existingEntry = MeetEntry::where([['meet_id', '=', $entryData->meetId],
                ['member_id', '=', $member->id]
            ])->get();

            if ($existingEntry->count() > 0) {
                $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
                $entry->status_id = $pendingStatus->id;
                $pendingReason = 'Existing entry found for this meet, so entry set to pending.';
                $entry->pending_reason = $pendingReason;
                $entry->saveOrFail();

                // Send confirmation email
                if (!property_exists($entryData, 'no_email') || !isset($entryData->no_email) || !$entryData->no_email) {
                    try {
                        $this->pendingEmail($entry);
                    } catch (Exception $e) {
                        \Sentry\captureMessage('Exception when sending pending email: ' . $e->getMessage());
                    }
                }

                $entry->entrydata = json_decode($entry->entrydata, true);
                $entry->status;
                return response()->json(['pending_entry' => $entry,
                    'status_id' => $pendingStatus->id,
                    'explanation' => $pendingReason,
                    'status_label' => $pendingStatus->label,
                    'status_description' => $pendingStatus->description], 200);
            }
        }

        $ageGroup = AgeGroup::where([['set', '=', 1],
            ['min', '<=', $age],
            ['max', '>=', $age],
            ['gender', '=', $gender],
            ['swimmers', '=', 1]])->first();

        if ($editMode) {
            $meetEntry = MeetEntry::find($entryData->edit_entry_id);
        } else {
            $meetEntry = new MeetEntry();
            $meetEntry->meet_id = $entryData->meetId;
            $meetEntry->member_id = $member->id;
            $meetEntry->age_group_id = $ageGroup->id;
        }

//        $meetEntry->meals = 0;
        $meetEntry->cancelled = 0;

        // Determine entry cost
        $meetDetails = Meet::where('id', '=', $entryData->meetId)->first();
        $meetEntry->cost = $this->calculateEntryFee($meetEntry, $entryData, $meetDetails);

        // Disability
        $classified = 0;

        if (isset($entryData->medicalDetails)) {
            switch ($entryData->medicalDetails->classification) {
                case 'classified':
                    $classified = 1;
                    break;
                case 'classification_provisional':
                    $classified = 2;
                    break;
                default:
                    $classified = 0;
            }
        }

        // TODO add better validation here.
        $meetEntry->disability_status = $classified;
        if ($classified != 0) {

            $classFreestyle = DisabilityClassification::where('classification', '=',
                $entryData->medicalDetails->classFreestyle)->first();
            $classBreaststroke = DisabilityClassification::where('classification', '=',
                $entryData->medicalDetails->classFreestyle)->first();
            $classMedley = DisabilityClassification::where('classification', '=',
                $entryData->medicalDetails->classMedley)->first();

            if ($classFreestyle != NULL) {
                $meetEntry->disability_s_id = $classFreestyle->id;
            }

            if ($classBreaststroke != NULL) {
                $meetEntry->disability_sb_id = $classBreaststroke->id;
            }

            if ($classMedley != NULL) {
                $meetEntry->disability_sm_id = $classMedley->id;
            }

        }

        if (isset($entryData->medicalDetails)) {
            // Does the entrant have a medical condition that requires stroke dispensation
            if ($entryData->medicalDetails->dispensation == "true") {
                $meetEntry->medical_condition = 1;
            } else {
                $meetEntry->medical_condition = 0;
            }

            // Does the entrant have a medical certificate for the stroke dispensation
            if ($entryData->medicalDetails->medicalCertificate == "") {
                $meetEntry->medical = 0;
            } elseif ($entryData->medicalDetails->medicalCertificate == "true") {
                $meetEntry->medical = 1;
            }

            // Does the entrant have a medical condition that may affect safety?
            if ($entryData->medicalDetails->medicalCondition == "true") {
                $meetEntry->medical_safety = 1;
            } else {
                $meetEntry->medical_safety = 0;
            }

            $meetEntry->medical_details = $entryData->medicalDetails->medicalDetails;
        }

        if (isset($entryData->mealMerchandiseDetails)) {
            $meetEntry->meals = $entryData->mealMerchandiseDetails->meals;
            $meetEntry->meals_comments = $entryData->mealMerchandiseDetails->mealComments;
        }

        // Get club
        if ($membershipDetails->club_selector != "" && $membershipDetails->club_selector != 'other') {
            $meetEntry->club_id = intval($membershipDetails->club_selector);
        } else {
            $club = Club::where('code', '=', $membershipDetails->club_code)->first();

            if ($club === NULL) {
                $club = Club::where('code', '=', 'UNAT')->first();

                if ($club === NULL) {
                    $meetEntry->club_id = NULL;
                } else {
                    $meetEntry->club_id = $club->id;
                }
            } else {
                $meetEntry->club_id = $club->id;
            }

        }

        $meetEntry->code = $entry->code;
        $meetEntry->incomplete_entry_id = $entry->id;
        $meetEntry->saveOrFail();
        $meetEntry->refresh();
        $meetEntryId = $meetEntry->id;

        $entry->meet_entry_id = $meetEntryId;
        $entry->saveOrFail();
        $entry->refresh();

        // Add events

        if ($editMode) {
            $existingMeetEntryEvents = MeetEntryEvent::where('meet_entry_id', '=', $meetEntryId);

            foreach ($existingMeetEntryEvents as $e) {
                $e->cancelled = true;
                $e->saveOrFail();
            }

        }

        if (isset($entryData->mealMerchandiseDetails)) {

            # Handle merchandise
            if (isset($entryData->mealMerchandiseDetails->merchandiseItems)) {
                if (count($entryData->mealMerchandiseDetails->merchandiseItems) > 0) {

                    $merchandiseOrder = new MeetEntryOrder();
                    $merchandiseOrder->meet_entries_id = $meetEntryId;
                    $merchandiseOrder->meet_id = $entryData->meetId;
                    $merchandiseOrder->member_id = $member->id;
                    $merchandiseOrder->total_exgst = 0;
                    $merchandiseOrder->total_gst = 0;
                    $merchandiseOrder->saveOrFail();
                    $merchandiseOrder->refresh();

                    $total_exgst = 0;
                    $total_gst = 0;

                    foreach ($entryData->mealMerchandiseDetails->merchandiseItems as $i) {
                        $merchandiseId = $i->merchandiseId;
                        $qty = $i->qty;

                        $merchandiseDetails = MeetMerchandise::find($merchandiseId);
                        if ($merchandiseDetails !== NULL) {
                            $merchandiseOrderItem = new MeetEntryOrderItem();
                            $merchandiseOrderItem->meet_merchandise_id = $merchandiseId;
                            $merchandiseOrderItem->meet_entry_orders_id = $merchandiseOrder->id;
                            $merchandiseOrderItem->qty = $qty;
                            $merchandiseOrderItem->price_each_exgst = $merchandiseDetails->exgst;
                            $merchandiseOrderItem->price_total_exgst = $qty * $merchandiseDetails->exgst;

                            $total_exgst += $qty * $merchandiseDetails->exgst;

                            if ($merchandiseDetails->gst_applicable) {
                                $merchandiseOrderItem->price_total_gst = $qty * ($merchandiseDetails->exgst + $merchandiseDetails->gst);
                                $merchandiseOrderItem->gst_applied = true;
                                $total_gst += $merchandiseOrderItem->price_total_gst;
                            } else {
                                $merchandiseOrderItem->price_total_gst = $qty * $merchandiseDetails->exgst;
                                $merchandiseOrderItem->gst_applied = false;
                                $total_gst += $merchandiseOrderItem->price_total_exgst;
                            }

                            $merchandiseOrderItem->saveOrFail();
                            $merchandiseOrderItem->refresh();

                        }
                    }

                    $merchandiseOrder->total_exgst = $total_exgst;
                    $merchandiseOrder->total_gst = $total_gst;
                    $merchandiseOrder->saveOrFail();
                    $merchandiseOrder->refresh();

                }
            }
        }

        foreach ($entryData->entryEvents as $eventEntry) {
            if ($editMode) {
                $meetEntryEvent = MeetEntryEvent::where(['meet_entry_id', '=', $meetEntryId],
                    ['event_id', '=', $eventEntry->event_id])->first();
            } else {
                $meetEntryEvent = new MeetEntryEvent();
                $meetEntryEvent->meet_entry_id = $meetEntryId;
                $meetEntryEvent->member_id = $member->id;
                $meetEntryEvent->event_id = $eventEntry->event_id;
            }
            $meetEntryEvent->cancelled = false;
            $meetEntryEvent->scratched = false;
            $meetEntryEvent->seedtime = $eventEntry->seedtime;
            $meetEntryEvent->saveOrFail();
        }

        $meetEntryCreated = MeetEntry::find($meetEntryId);

        // TODO: this seems wrong
        $meetEntryCreated['events'] = MeetEntryEvent::where('meet_entry_id', '=', $meetEntryId);

        // Send confirmation email
        if (!property_exists($entryData, 'no_email') || !isset($entryData->no_email) || !$entryData->no_email) {
            try {
                $meetEntryConfirmation = MeetEntry::find($meetEntryId);
                $this->confirmationEmail($meetEntryConfirmation);
            } catch (Exception $e) {
                \Sentry\captureException($e);
                \Sentry\captureMessage('Exception when sending confirmation email: ' . $e->getMessage());
            }
        }

        // Don't
        if (! $editMode) {
            if (isset($entryData->paymentOptions)) {
                switch ($entryData->paymentOptions->paymentOption) {
                    case 'club':
                        // set to Pending Club Payment
                        $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending Club Payment')->first();
                        $entry->status_id = $pendingStatus->id;
                        $entry->saveOrFail();
                        $status = $pendingStatus->id;

                        $meetEntryStatus = new MeetEntryStatus();
                        $meetEntryStatus->entry_id = $meetEntryId;
                        $meetEntryStatus->code = $pendingStatus->id;
                        $meetEntryStatus->saveOrFail();

                        break;
                    case 'later':
                    case 'paypal':
                        // set to Awaiting Payment
                        $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Awaiting Payment')->first();
                        $entry->status_id = $pendingStatus->id;
                        $entry->saveOrFail();
                        $status = $pendingStatus->id;

                        $meetEntryStatus = new MeetEntryStatus();
                        $meetEntryStatus->entry_id = $meetEntryId;
                        $meetEntryStatus->code = $pendingStatus->id;
                        $meetEntryStatus->saveOrFail();

                        break;
                }
            } else {
                // set to Awaiting Payment
                $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Awaiting Payment')->first();
                $entry->status_id = $pendingStatus->id;
                $entry->saveOrFail();
                $status = $pendingStatus->id;

                $meetEntryStatus = new MeetEntryStatus();
                $meetEntryStatus->entry_id = $meetEntryId;
                $meetEntryStatus->code = $pendingStatus->id;
                $meetEntryStatus->saveOrFail();
            }
        }

        // Handle any payment
        $paypalPayments = PayPalPayment::where('meet_entries_incomplete_id', '=', $entry->id)->get();

        foreach ($paypalPayments as $p) {
            // Update the PayPalPayment record to link it to the meet entry created
            $p->meet_entry_id = $meetEntryCreated->id;
            $p->saveOrFail();

            // Add a Meet Entry Payment record
            $payment = new MeetEntryPayment();
            $payment->entry_id = $meetEntryCreated->id;
            $payment->member_id = $meetEntryCreated->member_id;
            $payment->received = $p->created_at;
            $payment->amount = $p->paid;
            $payment->comment = "PayPal Invoice " . $p->invoice_id;
            $payment->saveOrFail();

            // TODO: Better handle cost
            if ($meetEntry->cost == $p->paid) {
                $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Accepted')->first();
                $entry->status_id = $pendingStatus->id;
                $entry->saveOrFail();
                $status = $pendingStatus->id;

                $meetEntryStatus = new MeetEntryStatus();
                $meetEntryStatus->entry_id = $meetEntryId;
                $meetEntryStatus->code = $pendingStatus->id;
                $meetEntryStatus->saveOrFail();
            }
        }

        $entry->finalised_at = date('Y-m-d H:i:s');
        $entry->saveOrFail();

        $statusCode = MeetEntryStatusCode::find($status);

        $meetEntryCreated->member;
        $meetEntryCreated->member->emergency;
        if ($meetEntryCreated->member->emergency != NULL) {
            $meetEntryCreated->member->emergency->phone;
        }
        $meetEntryCreated->member->phones;
        $meetEntryCreated->member->emails;
        $meetEntryCreated->member->memberships;

        $status = MeetEntryStatus::where('entry_id', '=', $meetEntryCreated->id)
            ->orderBy('id', 'DESC')
            ->first();
        if ($status != NULL) {
            $status->status;
            $meetEntryCreated['status'] = $status;
        }

        if ($meetEntryCreated->disability_s_id != NULL) {
            $meetEntryCreated->disability_s;
        }
        if ($meetEntryCreated->disability_sb_id != NULL) {
            $meetEntryCreated->disability_sb;
        }
        if ($meetEntryCreated->disability_sm_id != NULL) {
            $meetEntryCreated->disability_sm;
        }

        if ($meetEntryCreated->club_id !== NULL) {
            $meetEntryCreated->club;
        }

        $meetEntryCreated->age_group;
        $meetEntryCreated->meet;
        $meetEntryCreated->events;
        $meetEntryCreated->payments;

        foreach($meetEntryCreated->events as $e) {
            $e->event;
        }

        return response()->json(['meet_entry' => $meetEntryCreated,
            'status_id' => $status,
            'status_label' => $statusCode->label,
            'status_description' => $statusCode->description], 200);
    }

    private function calculateEntryFee($meetEntry, $entryData, $meetDetails) {
        $entryCost = 0;
        $numIndividualEvents = 0;
        $entryCost += $meetDetails->meetfee;

        // Fees for individual events
        foreach ($entryData->entryEvents as $eventEntry) {
            foreach ($meetDetails->events as $e) {
                if ($e->id == $eventEntry->event_id) {

                    if ($e->eventfee === NULL || $e->eventfee === 0) {
                        // Is there a number of included events set
                        if ($e->legs === 1) {
                            $numIndividualEvents++;
                            if ($numIndividualEvents > $meetDetails->included_events) {
                                if ($meetDetails->included_events !== NULL && $meetDetails->extra_event_fee !== NULL) {
                                    $entryCost += $meetDetails->extra_event_fee;
                                }
                            }
                        }
                    } else {
                        if ($e->legs === 1) {
                            $entryCost += $e->eventfee;
                        }
                    }
                }
            }
        }

        if (isset($entryData->mealMerchandiseDetails)) {
            $entryCost += $entryData->mealMerchandiseDetails->meals * $meetDetails->mealfee;

            if (isset($entryData->mealMerchandiseDetails->merchandiseItems)) {
                foreach ($entryData->mealMerchandiseDetails->merchandiseItems as $m) {
                    $merchandiseDetails = MeetMerchandise::find($m->merchandiseId);

                    $itemCost = 0;

                    if ($merchandiseDetails !== NULL) {
                        $itemCost = $merchandiseDetails->total_price * $m->qty;
                    }

                    $entryCost += $itemCost;
                }
            }
        }

        return $entryCost;
    }

    public function getSubmittedEntries() {
        $entries = MeetEntry::where('member_id', '=', $this->user->member)
            ->orderBy('meet_id', 'DESC')
            ->get();

        foreach ($entries as $e) {
            $e['meet'] = $e->meet;

            if (isset($e['meet'])) {
                $e['meet']->events;
            }

            foreach ($e->events as $event) {
                $event->event;
            }

            $e->club;
            $e->member;
            $e->age_group;
            $e->lodged_user;
            $e->disability_s;
            $e->disability_sb;
            $e->disability_sm;
            $e->payments;
            $e['emails'] = $e->emails;

            $status = MeetEntryStatus::where('entry_id', '=', $e->id)
                ->orderBy('id', 'DESC')
                ->first();
            if ($status != NULL) {
                $status->status;
                $e['status'] = $status;
            }

            $e['status_history'] = MeetEntryStatus::where('entry_id', '=', $e->id)
                ->orderBy('id', 'DESC')->get();

        }

        return response()->json($entries);
    }

    public function getSubmittedEntriesByMeet($meetId) {
        if (!$this->isAuthorised($meetId)) {
            return response()->json([
                'error' => 'You must be a meet organiser for this meet or an admin to access all entries to this meet',
                'meetId' => $meetId,
                'user' => $this->request->user()
            ], 403);
        }

        $meet = Meet::with(['events'])->find(intval($meetId));

        $entries = MeetEntry::with(['member', 'events', 'club', 'age_group', 'payments', 'disability_s',
            'disability_sb', 'disability_sm',
            'member',
            'member.phones',
            'member.emails',
            'member.memberships'])
            ->where('meet_id', '=', $meetId)->get();

//        foreach ($entries as $entry) {
//            $entry->member;
//            $entry->member->emergency;
//            if ($entry->member->emergency != NULL) {
//                $entry->member->emergency->phone;
//            }
//            $entry->member->phones;
//            $entry->member->emails;
//            $entry->member->memberships;
//
//            $status = MeetEntryStatus::where('entry_id', '=', $entry->id)
//                ->orderBy('id', 'DESC')
//                ->first();
//            if ($status != NULL) {
//                $status->status;
//                $entry['status'] = $status;
//            }
//
////            if ($entry->disability_s_id != NULL) {
////                $entry->disability_s;
////            }
////            if ($entry->disability_sb_id != NULL) {
////                $entry->disability_sb;
////            }
////            if ($entry->disability_sm_id != NULL) {
////                $entry->disability_sm;
////            }
//
////            if ($entry->club_id !== NULL) {
////                $entry->club;
////            }
//
////            $entry->age_group;
//            //  $entry->meet;
////            $entry->events;
////            $entry->payments;
//
////            foreach($entry->events as $e) {
//////                $e->event;
////            }
//        }

        return response()->json(['success' => true,
            'meet_id' => intval($meetId),
            'meet' => $meet,
            'meet_entries' => $entries]);
    }

    public function getPendingEntriesByMeet($meetId)
    {
        if (!$this->isAuthorised($meetId)) {
            return response()->json([
                'error' => 'You must be a meet organiser for this meet or an admin to access pending entries to this meet',
                'meetId' => intval($meetId),
                'user' => $this->request->user()
            ], 403);
        }

        $statusIncomplete = MeetEntryStatusCode::where('label', '=', 'Incomplete')->first()->id;

        $entries = MeetEntryIncomplete::where('meet_id', '=', $meetId)->get();

        foreach ($entries as $entry) {
            $entry->entrydata = json_decode($entry->entrydata, true);
            $entry->status;
        }

        return response()->json(['success' => true,
            'pending_entries' => $entries], 200);

    }

    public function getMeetEntryByCode($code) {
        $entry = MeetEntry::where('code', '=', $code)->first();
        return $this->getMeetEntryReturn($entry);
    }

    public function getMeetEntry($id)
    {
        $entry = MeetEntry::find($id);

        if ($this->user == NULL) {
            return response()->json(['success' => false,
                'message' => 'Unauthorised.'], 403);
        }

        if ($entry->user_id != NULL && !$this->user->is_admin) {
            if ($this->user->member !== $entry->member_id) {
                return response()->json(['success' => false,
                    'message' => 'You can only access your entries'], 403);
            }
        }

        return $this->getMeetEntryReturn($entry);
    }

    private function getMeetEntryReturn($entry) {

        if ($entry == null) {
            return null;
        }

        $entry->member;
        $entry->member->emergency;
        if ($entry->member->emergency != NULL) {
            $entry->member->emergency->phone;
        }
        $entry->member->phones;
        $entry->member->emails;
        $entry->member->memberships;

        $status = MeetEntryStatus::where('entry_id', '=', $entry->id)
            ->orderBy('id', 'DESC')
            ->first();
        if ($status != NULL) {
            $status->status;
            $entry['status'] = $status;
        }

        if ($entry->disability_s_id != NULL) {
            $entry->disability_s;
        }
        if ($entry->disability_sb_id != NULL) {
            $entry->disability_sb;
        }
        if ($entry->disability_sm_id != NULL) {
            $entry->disability_sm;
        }

        if ($entry->club_id !== NULL) {
            $entry->club;
        }

        $entry->age_group;
        $entry->meet;
        $entry->events;
        $entry->payments;

        $entry->emails;

        foreach($entry->events as $e) {
            $e->event;
        }

        $merchandise = MeetEntryOrder::where('meet_entries_id', '=', $entry->id)->get();
        foreach ($merchandise as $order) {
            $items = $order->items;
            foreach ($items as $item) {
                $item->merchandise;
            }
        }

        return response()->json(['success' => true,
            'meet_entry' => $entry,
            'merchandise' => $merchandise]);
    }

    public function getSubmittedEntriesByMemberNumber($number) {
        if ($this->user->is_admin != 1) {
            return response()->json(['success' => false,
                'message' => 'You do not have permission to view Meet Entries.'], 403);
        }

        $member = Member::where('number', '=', $number)->first();
        if ($member == NULL) {
            return response()->json(['success' => false,
                'message' => 'Member not found.'], 404);
        }

        $entries = MeetEntry::where('member_id', '=', $member->id)->get();

        foreach ($entries as $entry) {
            $entry->member;
            $emergency = $entry->member->emergency;
            if ($emergency !== NULL) {
                $entry->member->emergency->phone;
            }
            $entry->member->phones;
            $entry->member->emails;
            $entry->member->memberships;

            $status = MeetEntryStatus::where('entry_id', '=', $entry->id)
                ->orderBy('id', 'DESC')
                ->first();
            if ($status != NULL) {
                $status->status;
                $entry['status'] = $status;
            }

            if ($entry->disability_s_id != NULL) {
                $entry->disability_s;
            }
            if ($entry->disability_sb_id != NULL) {
                $entry->disability_sb;
            }
            if ($entry->disability_sm_id != NULL) {
                $entry->disability_sm;
            }

            if ($entry->club_id !== NULL) {
                $entry->club;
            }

            $entry->age_group;
            $entry->meet;
            $entry->events;
            $entry->payments;

            foreach($entry->events as $e) {
                $e->event;
            }
        }

        return response()->json(['success' => true,
            'member_number' => $number,
            'meet_entries' => $entries]);
    }

    public function approve_pending($pendingId) {
        if ($this->user->is_admin != 1) {
            return response()->json(['success' => false,
                'message' => 'You do not have permission to view Meet Entries.'], 403);
        }

        $entry = MeetEntryIncomplete::find($pendingId);
        if ($entry == NULL) {
            return response()->json(['success' => false,
                'message' => 'Unable to find this pending meet entry.'], 404);
        }

        return $this->createOrUpdateEntry($entry, false);
    }

    public function processed_pending($pendingId) {
        if ($this->user->is_admin != 1) {
            return response()->json(['success' => false,
                'message' => 'You do not have permission to view Meet Entries.'], 403);
        }

        $entry = MeetEntryIncomplete::find($pendingId);
        if ($entry == NULL) {
            return response()->json(['success' => false,
                'message' => 'Unable to find this pending meet entry.'], 404);
        }

        $entry->finalised_at =  date('Y-m-d H:i:s');
        $entry->saveOrFail();
        return response()->json(['success' => true,
            'entry' => $entry], 200);
    }

    public function isAuthorised($meet_id) {
        $user = $this->request->user();
        $memberId = $user->member;
        $isMeetOrganiser = false;

        if ($user->member !== NULL) {

            $meetAccess = MeetAccess::where([['member_id', '=', $memberId],
                ['meet_id', '=', $meet_id]])->first();

            if ($meetAccess !== NULL) {
                $isMeetOrganiser = true;
            }

        }

        if (!$isMeetOrganiser) {
            if (!$user->is_admin) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public function sendConfirmationEmail($id) {

        $entryId = intval($id);
        $entry = MeetEntry::find($entryId);

        if ($entry == NULL) {
            return response()->json(['success' => false,
                'message' => 'Meet entry not found.'], 404);
        }

        try {
            $emailAddress = $this->confirmationEmail($entry);
            return response()->json([
                'success' => true,
                'message' => 'Meet Entry Confirmation email sent to ' . $emailAddress . '.'
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false,
                'message' => 'Unable to send email for entry id ' . $entryId . ': ' . $e->getMessage()], 400);
        }

    }

    public function sw_formatSecs($secTime) {

        if (!strpbrk($secTime, '.')) {

            $secTimeSecs = $secTime;
            $secTimeMs = "00";

        } else {

            list($secTimeSecs, $secTimeMs) = explode('.', $secTime);

            if (strlen($secTimeMs) == 1) {

                $secTimeMs = $secTimeMs . '0';

            }

        }

        if ($secTime < 60) {
            $secTimeDisp = ($secTimeSecs % 60) . '.' . $secTimeMs;
        } else {
            $secTimeDisp = floor($secTimeSecs / 60) . ':' . sprintf("%02d", ($secTimeSecs % 60)) . '.' . $secTimeMs;
        }

        if ($secTimeDisp == "0:00.00" || $secTimeDisp == '0.00') {

            $secTimeDisp = "NT";

        }

        return $secTimeDisp;

    }

    public function confirmationEmail($entry) {

        $entry->member;
        $meet = $entry->meet;
        $entry->events;
        $entry->member->emails;
        $entry->member->memberships;
        $club = $entry->club;

        $meetName = $entry->meet->meetname;
        $emails = $entry->member->emails;

        $events = array();

        $eventDetails = null;

        foreach ($entry->events as $e) {
            $entryEvent = $e->event;
            $eventItem = array();
            $eventItem['prognumber'] = $entryEvent->prognumber . $entryEvent->progsuffix;
            $eventItem['details'] = $entryEvent->eventDistance->distance . ' ' . $entryEvent->eventDiscipline->discipline;
            $eventItem['seedtime'] = $this->sw_formatSecs($e->seedtime);
            array_push($events, $eventItem);
        }

        $emailAddress = trim($emails->last()->address);

        $memberDisplayName = $entry->member->firstname . ' ' . $entry->member->surname;
        $entrantName = $memberDisplayName;

        $meetName = $meet->meetname;
        $meetDate = '';
        $mealName = '';
        $mealsOrdered = 0;
        $total = 0;
        $totalgst = 0;

        if ($meet->startdate != $meet->enddate) {
            $startDt = new DateTime($meet->startdate);
            if ($meet->enddate != NULL) {
                $endDt = new DateTime($meet->enddate);
                $meetDate = $startDt->format('l j F, Y') . ' - ' . $endDt->format('l j F, Y');
            } else {
                $meetDate = $startDt->format('l j F, Y');
            }
        } else {
            $startDt = new DateTime($meet->startdate);
            $meetDate = $startDt->format('l j F, Y');
        }

        $items = array();

        $orders = $entry->orders;

        foreach ($orders as $o) {
            $orderItems = $o->items()->get();
            foreach ($orderItems as $i) {
                $merchandise = $i->merchandise;
                $item = array();
                $item['itemNumber'] = $i->merchandise->sku;
                $item['itemName'] = $i->merchandise->item_name;
                $item['unitPrice'] = $i->merchandise->exgst + $i->merchandise->gst;
                $item['qty'] = $i->qty;
                $item['subtotal'] = ($i->merchandise->exgst + $i->merchandise->gst) * $i->qty;
                array_push($items, $item);

                $totalgst += $i->merchandise->gst * $i->qty;
                $total += $item['subtotal'];
            }
        }

        $clubName = '';
        if ($club != NULL) {
            $clubName = $club->clubname . ' (' . $club->code . ')';
        }

        $viewEntry = env('SITE_BASE') . '/entry-confirmation/' . $entry->code;

        $data = array('entry' => $entry,
            'viewEntry' => $viewEntry,
            'meetname' => $meetName,
            'entrantName' => $entrantName,
            'clubName' => $clubName,
            'mealName' => $mealName,
            'mealsOrdered' => $mealsOrdered,
            'meetDate' => $meetDate,
            'total' => $total,
            'totalgst' => $totalgst,
            'events' => $events,
            'items' => $items);

        Mail::send('entryconfirmation', $data, function ($message) use ($meetName, $emailAddress, $memberDisplayName) {
            $message->to($emailAddress, $memberDisplayName)->subject('Entry Confirmation - ' . $meetName);
            $message->from('recorder@mastersswimmingqld.org.au', 'MSQ Quick Entry');
        });

        $entryEmail = new MeetEntryEmails();
        $entryEmail->meet_entry_id = $entry->id;
        $entryEmail->email_address = $emailAddress;
        $objDateTime = new DateTime('NOW');
        $entryEmail->timestamp = $objDateTime->format('Y-m-d H:i:s');
        $entryEmail->save();

        return $emailAddress;
    }

    public function applyPayment($id) {
        if ($this->user->is_admin != 1) {
            return response()->json(['success' => false,
                'message' => 'You do not have permission to view Meet Entries.'], 403);
        }

        $entry = MeetEntry::find($id);
        if ($entry == NULL) {
            return response()->json(['success' => false,
                'message' => 'Unable to find this entry.'], 404);
        }

        $payment = new MeetEntryPayment();
        $payment->entry_id = $id;
        $payment->member_id = $entry->member_id;
        $payment->received = $this->request->input('received');
        $payment->method = $this->request->input('method');
        $payment->comment = $this->request->input('comment');
        $payment->amount = $this->request->input('amount');

        try {
            $payment->saveOrFail();

            if ($this->totalEntryPayments($id) >= $entry->cost) {
                // Set status to Accepted
                $acceptedStatus = MeetEntryStatusCode::where('label', '=', 'Accepted')->first();

                $meetEntryStatus = new MeetEntryStatus();
                $meetEntryStatus->entry_id = $id;
                $meetEntryStatus->code = $acceptedStatus->id;
                $meetEntryStatus->saveOrFail();
            }

        } catch (Exception $e) {
            \Sentry\captureException($e);
            \Sentry\captureMessage('Exception when applying payment: ' . $e->getMessage());

            return response()->json(['success' => false,
                'message' => 'Unable to apply payment for entry id ' . $id . ': ' . $e->getMessage()], 400);
        }
    }

    public function totalEntryPayments($entryId) {
        $totalPayments = 0;

        $entry = MeetEntry::find($entryId);
        $payments = $entry->payments;

        foreach ($payments as $p) {
            $totalPayments += $p->amount;
        }
        
        return $totalPayments;
    }

    public function sendPaymentEmailMeetEntry($id) {
        $entryId = intval($id);
        $entry = MeetEntry::find($id);

        if ($entry == NULL) {
            return response()->json(['success' => false,
                'message' => 'Meet entry not found.'], 404);
        }

        try {
            $emailAddress = $this->paymentEmailMeetEntry($entry);
            return response()->json([
                'success' => true,
                'message' => 'Meet entry payment link email sent to ' . $emailAddress . '.'
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false,
                'message' => 'Unable to send payment link email for entry id ' . $entryId . ': ' . $e->getMessage()], 400);
        }

        return $emailAddress;
    }

    public function paymentEmailMeetEntry($entry) {

        $meet = $entry->meet;
        $meetName = $meet->meetname;
        $emails = $entry->member->emails;
        $emailAddress = trim($emails->last()->address);

        $memberDisplayName = $entry->member->firstname . ' ' . $entry->member->surname;
        $entrantName = $memberDisplayName;

        $viewEntry = env('SITE_BASE') . '/entry-confirmation/' . $entry->code;
        $payEntry = env('SITE_BASE') . '/entry-confirmation/' . $entry->code . '?pay=paypal';

        $data = array('entry' => $entry,
            'viewEntry' => $viewEntry,
            'payEntry' => $payEntry,
            'meetname' => $meetName );

        Mail::send('meetentrypayment', $data, function ($message) use ($meetName, $emailAddress, $memberDisplayName) {
            $message->to($emailAddress, $memberDisplayName)->subject('Make a Payment - ' . $meetName);
            $message->from('recorder@mastersswimmingqld.org.au', 'MSQ Quick Entry');
        });
    }

    public function sendPendingConfirmationEmail($id) {

        $entryId = intval($id);

        $entry = MeetEntryIncomplete::find($entryId);

        if ($entry == NULL) {
            return response()->json(['success' => false,
                'message' => 'Meet entry not found.'], 404);
        }

        try {
            $emailAddress = $this->pendingEmail($entry);
            return response()->json([
                'success' => true,
                'message' => 'Meet Entry Confirmation email sent to ' . $emailAddress . '.'
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false,
                'message' => 'Unable to send email for entry id ' . $entryId . ': ' . $e->getMessage()], 400);
        }

    }

    public function pendingEmail($pendingEntry) {

        $entryData = json_decode($pendingEntry['entrydata'], true);
        $emailAddress = trim($entryData['entrantDetails']['entrantEmail']);
        $memberDisplayName = $entryData['entrantDetails']['entrantFirstName'] . ' ' . $entryData['entrantDetails']['entrantSurname'];
        $entrantName = $entryData['entrantDetails']['entrantFirstName'] . ' ' . $entryData['entrantDetails']['entrantSurname'];
        $clubName = '';

        if (array_key_exists('club_name', $entryData['membershipDetails'])) {
            if ($entryData['membershipDetails']['club_name'] != NULL) {
                $clubName = $entryData['membershipDetails']['club_name'];
            }
        }

        $meetId = intval($pendingEntry['meet_id']);

        $meet = Meet::find($meetId);
        $meetName = $meet->meetname;
        $meetDate = '';
        $mealName = '';
        $mealsOrdered = 0;
        $total = 0;
        $totalgst = 0;

        $items = array();

        if ($meet->mealname != NULL && $meet->mealname != '') {
            if (array_key_exists('mealMerchandiseDetails', $entryData))  {
                if (array_key_exists('meals', $entryData['mealMerchandiseDetails'])) {
                    $mealName = $meet->mealname;
                    $mealsOrdered = intval($entryData['mealMerchandiseDetails']['meals']);
                }

                if (array_key_exists('merchandiseItems', $entryData['mealMerchandiseDetails'])) {
                    foreach ($entryData['mealMerchandiseDetails']['merchandiseItems'] as $m) {
                        $item = array();
                        $item['qty'] = $m['qty'];

                        $itemDetails = MeetMerchandise::find($m['merchandiseId']);
                        $item['itemNumber'] = $itemDetails->sku;
                        $item['itemName'] = $itemDetails->item_name;
                        $item['unitPrice'] = $itemDetails->exgst + $itemDetails->gst;;
                        $item['subtotal'] = $item['unitPrice'] * $m['qty'];

                        $totalgst += $itemDetails->gst * $m['qty'];
                        $total += $item['subtotal'];

                        array_push($items, $item);

                    }
                }
            }
        }

        if ($meet->startdate != $meet->enddate) {
            $startDt = new DateTime($meet->startdate);
            if ($meet->enddate != NULL) {
                $endDt = new DateTime($meet->enddate);
                $meetDate = $startDt->format('l j F, Y') . ' - ' . $endDt->format('l j F, Y');
            } else {
                $meetDate = $startDt->format('l j F, Y');
            }
        } else {
            $startDt = new DateTime($meet->startdate);
            $meetDate = $startDt->format('l j F, Y');
        }

        $events = array();

        foreach ($entryData['entryEvents'] as $e) {
            $entryEvent = array();
            $entryEvent['prognumber'] = $e['program_no'];
            $entryEvent['details'] = $e['distance'] . ' ' . $e['discipline'];
            $entryEvent['seedtime'] = $this->sw_formatSecs($e['seedtime']);
            array_push($events, $entryEvent);
        }

        $data = array('meetname' => $meetName,
            'meetDate' => $meetDate,
            'entrantName' => $entrantName,
            'clubName' => $clubName,
            'mealName' => $mealName,
            'mealsOrdered' => $mealsOrdered,
            'events' => $events,
            'total' => $total,
            'totalgst' => $totalgst,
            'items' => $items);
        Mail::send('pendingentryconfirmation', $data, function ($message) use ($meetName, $emailAddress, $memberDisplayName) {
            $message->to($emailAddress, $memberDisplayName)->subject('Entry Received - ' . $meetName);
            $message->from('recorder@mastersswimmingqld.org.au', 'MSQ Quick Entry');
        });

        // Store email data
        try {
            $email_data = array();
            $email_dt = new DateTime('NOW');
            $email_data['datetime'] = $email_dt->format(DateTime::ISO8601);
            $email_data['email_address'] = $emailAddress;

            if (!array_key_exists('email_confirmations', $entryData)) {
                $entryData['email_confirmations'] = array();
            }

            array_push($entryData['email_confirmations'], $email_data);

            $pendingEntry->entrydata = json_encode($entryData);
            $pendingEntry->save();
        } catch (Exception $e) {
            \Sentry\captureException($e);
            \Sentry\captureMessage('Exception when sending pending confirmation email: ' . $e->getMessage());
        }

        return $emailAddress;
    }
}