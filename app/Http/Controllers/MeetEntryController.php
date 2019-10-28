<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\DisabilityClassification;
use App\MeetEntry;
use App\MeetEntryEvent;
use App\MeetEntryPayment;
use App\MeetEntryStatus;
use App\MeetEntryIncomplete;
use App\MeetEntryStatusCode;
use App\Member;
use App\Meet;
use App\Club;
use App\AgeGroup;

use App\PayPalPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use mysql_xdevapi\Exception;

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
            //$uniqueCode = true;
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
            array_push($outputEntries, $e);
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

            // User isn't an MSA member so don't try to create an entry for them
            if ($membershipDetails->member_type != 'msa') {
                $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
                $entry->status_id = $pendingStatus->id;
                $pendingReason = 'Logged in non-msa member, entry set to pending.';
                $entry->pending_reason = $pendingReason;
                $entry->saveOrFail();
                $entry->entrydata = json_decode($entry->entrydata, true);
                return response()->json(['pending_entry' => $entry,
                    'status_id' => $pendingStatus->id,
                    'explanation' => $pendingReason,
                    'status_label' => $pendingStatus->label,
                    'status_description' => $pendingStatus->description], 200);
            }

            // Club id is set to other
            if ($membershipDetails->club_selector != 'other') {
                $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
                $entry->status_id = $pendingStatus->id;
                $pendingReason = 'Club ID is set to other';
                $entry->pending_reason = $pendingReason;
                $entry->saveOrFail();
                $entry->entrydata = json_decode($entry->entrydata, true);
                return response()->json(['pending_entry' => $entry,
                    'status_id' => $pendingStatus->id,
                    'explanation' => $pendingReason,
                    'status_label' => $pendingStatus->label,
                    'status_description' => $pendingStatus->description], 200);
            }

            // If member number isn't set leave the entry as pending
            if ($membershipDetails->member_number == '') {
                $memberSearch = Member::where('surname', '=', $entrantDetails->entrantSurname)
                    ->where('firstname', '=', $entrantDetails->entrantFirstName)
                    ->where('dob', '=', $entrantDetails->entrantDob)
                    ->first();

                if ($memberSearch == NULL) {
                    //
                    $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
                    $entry->status_id = $pendingStatus->id;
                    $pendingReason = 'Member number not provided, member not found.';
                    $entry->pending_reason = $pendingReason;
                    $entry->saveOrFail();
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
                $memberSearch = Member::where('number', '=', $membershipDetails->member_number)
                    ->first();

                if (!isset($memberSearch)) {

                    // Membership number supplied does not match entrant details
                    $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
                    $entry->status_id = $pendingStatus->id;
                    $pendingReason = 'Member number supplied not found.';
                    $entry->pending_reason = $pendingReason;
                    $entry->saveOrFail();
                    $entry->entrydata = json_decode($entry->entrydata, true);
                    return response()->json(['pending_entry' => $entry,
                        'status_id' => $pendingStatus->id,
                        'explanation' => $pendingReason,
                        'status_label' => $pendingStatus->label,
                        'status_description' => $pendingStatus->description], 200);

                } else {

                    if ($memberSearch->surname != $entrantDetails->entrantSurname ||
                        $memberSearch->dob != $entrantDetails->entrantDob) {

                        // Membership number supplied does not match entrant details
                        $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
                        $entry->status_id = $pendingStatus->id;
                        $pendingReason = 'Member number supplied does not match entrant details.';
                        $entry->pending_reason = $pendingReason;
                        $entry->saveOrFail();
                        $entry->entrydata = json_decode($entry->entrydata, true);
                        return response()->json(['pending_entry' => $entry,
                            'status_id' => $pendingStatus->id,
                            'explanation' => $pendingReason,
                            'status_label' => $pendingStatus->label,
                            'status_description' => $pendingStatus->description], 200);
                    }
                }
            }

            $entryMemberId = Member::where('number', '=', $membershipDetails->member_number)->first();

            // Allow entries by anyone
//            if ($this->user->member != $entryMemberId->id) {
//                $pendingStatus = MeetEntryStatusCode::where('label', '=', 'Pending')->first();
//                $entry->status_id = $pendingStatus->id;
//                $pendingReason = 'User linked member doesn\'t match entry member.';
//                $entry->pending_reason = $pendingReason;
//                $entry->saveOrFail();
//                $entry->entrydata = json_decode($entry->entrydata, true);
//                return response()->json(['pending_entry' => $entry,
//                    'status_id' => $pendingStatus->id,
//                    'explanation' => $pendingReason,
//                    'status_label' => $pendingStatus->label,
//                    'status_description' => $pendingStatus->description], 200);
//            }

//            return response()->json(['age' => $age,
//                'gender' => $gender,
//                'ageUpDate' => $ageUpDate,
//                'dob' => $member->dob]);

            // Does Member already have entry for this event?
            $editMode = false;
            if (property_exists($entryData, 'edit_mode') && isset($entry->edit_mode)
                && $entry->edit_mode) {
                $editMode = true;
            }

//            return response()->json(['pending_entry' => $entry], 200);

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

        $meetEntry->meals = 0;
        $meetEntry->cancelled = 0;

        // Determine entry cost
        $meetDetails = Meet::where('id', '=', $entryData->meetId)->first();
        $meetEntry->cost = $this->calculateEntryFee($meetEntry, $entryData, $meetDetails);

        // Disability
        $classified = 0;
        switch($entryData->medicalDetails->classification) {
            case 'classified':
                $classified = 1;
                break;
            case 'classification_provisional':
                $classified = 2;
                break;
            default:
                $classified = 0;
        }

        // TODO add better validation here.
        $meetEntry->disability_status = $classified;
        if ($classified != 0) {
            $meetEntry->disability_s_id = DisabilityClassification::where('classification', '=',
                $entryData->medicalDetails->classFreestyle)->first()->id;
            $meetEntry->disability_sb_id = DisabilityClassification::where('classification', '=',
                $entryData->medicalDetails->classBreaststroke)->first()->id;
            $meetEntry->disability_sm_id = DisabilityClassification::where('classification', '=',
                $entryData->medicalDetails->classMedley)->first()->id;
        }

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

        // Get club
        if ($membershipDetails->club_selector != "") {
            $meetEntry->club_id = intval($membershipDetails->club_selector);
        } else {
            $club = Club::where('code', '=', $membershipDetails->club_code)->first();
            $meetEntry->club_id = $club->id;
        }

        $meetEntry->code = $entry->code;
        $meetEntry->saveOrFail();
        $meetEntryId = $meetEntry->id;

        // Add events

        if ($editMode) {
            $existingMeetEntryEvents = MeetEntryEvent::where('meet_entry_id', '=', $meetEntryId);

            foreach ($existingMeetEntryEvents as $e) {
                $e->cancelled = true;
                $e->saveOrFail();
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
        $meetEntryCreated['events'] = MeetEntryEvent::where('meet_entry_id', '=', $meetEntryId);

        // Don't
        if (! $editMode) {
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
                case 'later':
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

        $entryCost += $meetDetails->meetfee;

        foreach ($entryData->entryEvents as $eventEntry) {
            foreach ($meetDetails->events as $e) {
                if ($e->id == $eventEntry->event_id) {
                    $entryCost += $e->eventfee;
                }
            }
        }

        return $entryCost;
    }

    public function getSubmittedEntries() {
        $entries = MeetEntry::where('member_id', '=', $this->user->member)->get();

        foreach ($entries as $e) {
            $e->events;
            $e->club;
            $e->member;
            $e->age_group;
            $e->lodged_user;
            $e->disability_s;
            $e->disability_sb;
            $e->disability_sm;
            $e->payments;

            $status = MeetEntryStatus::where('entry_id', '=', $e->id)
                ->orderBy('id', 'DESC')
                ->first();
            if ($status != NULL) {
                $status->status;
                $e['status'] = $status;
            }

        }

        return response()->json($entries);
    }

    public function getSubmittedEntriesByMeet($meetId) {
        if ($this->user->is_admin != 1) {
            return response()->json(['success' => false,
                    'message' => 'You do not have permission to view Meet Entries.'], 403);
        }

        $entries = MeetEntry::where('meet_id', '=', $meetId)->get();

        foreach ($entries as $entry) {
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

            foreach($entry->events as $e) {
                $e->event;
            }
        }

        return response()->json(['success' => true,
            'meet_id' => $meetId,
            'meet_entries' => $entries]);
    }

    public function getPendingEntriesByMeet($meetId)
    {
        if ($this->user->is_admin != 1) {
            return response()->json(['success' => false,
                'message' => 'You do not have permission to view Meet Entries.'], 403);
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

        foreach($entry->events as $e) {
            $e->event;
        }

        return response()->json(['success' => true,
            'meet_entry' => $entry]);
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
            $entry->member->emergency;
            $entry->member->emergency->phone;
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
}