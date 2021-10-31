<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Email;
use App\Meet;
use App\MeetAccess;
use App\MeetEvent;

use App\MeetPaymentMethod;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

class MeetController extends Controller {

    private $request;
    private $userId;
    private $user;

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

	public function showCurrentMeets(Request $request)
	{

//		$year = $request->input('year');
		$year = date('Y');

		if (isset($year)) {

			$meets = Meet::whereYear('startdate', $year)->get();

			foreach($meets as $m) {

			    $meetEvents = $m->events;

                foreach ($meetEvents as $e) {
                    $eventDistance = $e->distance;
                    $eventDiscipline = $e->discipline;
                }

                $meetEventGroups = $m->groups;

                $email = $m->email;
                $phone = $m->phone;

                if (isset($email)) {
                    $emailaddress = $email->address;
                }

                $meetDetails['sessions'] = $m->sessions;

                if (count($m->sessions) == 0) {
                    $m['events'] = $meetEvents;
                }

                foreach ($meetEventGroups as $g) {
                    $g['rules'] = $g->rules;
                    $g['events'] = $g->events;
                }

                $meetPaymentMethods = $m->paymentTypes;

                foreach ($meetPaymentMethods as $p) {
                    $p->paymentType;
                }

                $merchandise = $m->merchandise;
                foreach ($merchandise as $m) {
                    $m->images;
                }

			}

			return response()->json($meets);

		}

		return response()->json(Meet::all());
	}

	public function showOneMeet($id)
	{

		$meetDetails = Meet::find($id);

		if ($meetDetails == null) {
            return response()->json([
                'success' => false,
                'meet_id' => $id,
                'message' => 'Meet not found.'
            ], 404);
        }

		$meetEvents = $meetDetails->events;

		foreach ($meetEvents as $m) {
		    $eventDistance = $m->distance;
		    $eventDiscipline = $m->discipline;
        }

		$meetEventGroups = $meetDetails->groups;

		// TODO: only supply this is admin user accessing
        if ($this->user && $this->user->is_admin) {
            $access = $meetDetails->access;
            foreach ($access as $a) {
                $member = $a->member();
            }
        }

		$email = $meetDetails->email;
		$phone = $meetDetails->phone;
		if (isset($email)) {
            $emailaddress = $email->address;
        }

		$meetDetails['sessions'] = $meetDetails->sessions;

		if (count($meetDetails->sessions) == 0) {
            $meetDetails['events'] = $meetEvents;
        }

		foreach ($meetEventGroups as $g) {
		    $g['rules'] = $g->rules;
		    $g['events'] = $g->events;
		}

        $meetPaymentMethods = $meetDetails->paymentTypes;

        foreach ($meetPaymentMethods as $p) {
            $p->paymentType;
        }

        $merchandise = $meetDetails->merchandise;
        foreach ($merchandise as $m) {
            $m->images;
        }

		return response()->json($meetDetails);
	}

	public function getAllMeets()
    {
        $meets = Meet::orderBy('startdate', 'asc')->get();
        return response()->json($meets);
    }

	public function getEvents($id)
	{
		return response()->json(Meet::find($id)->events);
	}

	public function getEventGroups($id)
	{
		return response()->json(Meet::find($id)->groups);
	}

	private function getMeetFromArray($meet, $m) {
        $meet->meetname = $m['meetname'];

        if (strpos($m['startdate'], '/') !== false) {
            $dateObj = DateTime::createFromFormat('d/m/Y', $m['startdate']);
            $meet->startdate = $dateObj->format('Y-m-d');

        } else {
            $meet->startdate = $m['startdate'];
        }

        $meet->location = 'TBA';

        if (array_key_exists('enddate', $m)) {
            if (strpos($m['enddate'], '/') !== false) {
                $dateObj = DateTime::createFromFormat('d/m/Y', $m['enddate']);
                $meet->enddate = $dateObj->format('Y-m-d');
            } else {
                $meet->enddate = $m['enddate'];
            }
        }

        if (array_key_exists('deadline', $m)) {
            if (strpos($m['deadline'], '/') !== false) {
                $dateObj = DateTime::createFromFormat('d/m/Y', $m['deadline']);
                $meet->deadline = $dateObj->format('Y-m-d');
            } else {
                $meet->deadline = $m['deadline'];
            }
        }

        if (array_key_exists('contactname', $m)) {
            $meet->contactname = $m['contactname'];
        }

        if (array_key_exists('contactemail', $m)) {

            $email = Email::where('address', '=', $m['contactemail'])->first();
            if ($email != NULL) {
                $meet->contactemail = $email->id;
            } else {
                $email = new Email();
                $email->email_type = 1;
                $email->address = $m['contactemail'];
                $email->saveOrFail();
                $meet->contactemail = $email->id;
            }


        }

        if (array_key_exists('meetfee', $m)) {
            $meet->meetfee = $m['meetfee'];
        }

        if (array_key_exists('mealfee', $m)) {
            $meet->mealfee = $m['mealfee'];
        }

        if (array_key_exists('location', $m)) {
            $meet->location = $m['location'];
        }

        if (array_key_exists('status', $m)) {
            $meet->status = $m['status'];
        }

        if (array_key_exists('maxevents', $m)) {
            $meet->maxevents = $m['maxevents'];
        }

        if (array_key_exists('mealsincluded', $m)) {
            $meet->mealsincluded = $m['mealsincluded'];
        }

        if (array_key_exists('mealname', $m)) {
            $meet->mealname = $m['mealname'];
        }

        if (array_key_exists('massagefee', $m)) {
            $meet->massagefee = $m['massagefee'];
        }

        if (array_key_exists('programfee', $m)) {
            $meet->programfee = $m['programfee'];
        }

        if (array_key_exists('meetfee_nonmember', $m)) {
            $meet->meetfee_nonmember = $m['meetfee_nonmember'];
        }

        if (array_key_exists('minevents', $m)) {
            $meet->minevents = $m['minevents'];
        }

        if (array_key_exists('included_events', $m)) {
            $meet->included_events = $m['included_events'];
        }

        if (array_key_exists('extra_event_fee', $m)) {
            $meet->extra_event_fee = $m['extra_event_fee'];
        }

        if (array_key_exists('gst_applicable', $m)) {
            $meet->gst_applicable = $m['gst_applicable'];
        }

        if (array_key_exists('tax_notes', $m)) {
            $meet->tax_notes = $m['tax_notes'];
        }

        if (array_key_exists('logged_in_only', $m)) {
            $meet->logged_in_only = $m['logged_in_only'];
        }

        if (array_key_exists('guest_relays', $m)) {
            $meet->guest_relays = $m['guest_relays'];
        }

        return $meet;
    }

	public function createMeet() {
        $meet = new Meet();
        $m = $this->request->all();

        if (!$this->user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to create a meet!'
            ], 403);
        }

        $meet = $this->getMeetFromArray($meet, $m);
        $meet->save();

        return response()->json([
            'success' => true,
            'meet' => $meet], 200);

    }

    public function updateMeet($id) {
        $meet = Meet::find($id);
        $m = $this->request->all();

        if (!$this->user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit a meet!'
            ], 403);
        }

        $meet = $this->getMeetFromArray($meet, $m);
        $meet->save();

        return response()->json([
            'success' => true,
            'meet' => $meet], 200);

    }

    public function publishMeet($id) {
        $meet = Meet::find($id);
        $m = $this->request->all();

        if (!$this->user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit a meet!'
            ], 403);
        }

        if ($meet == NULL) {
            return response()->json([
                'success' => false,
                'message' => 'Meet not found!'
            ], 403);
        }

        if (array_key_exists('publish', $m)) {
            if ($m['publish']) {
                $meet->status = 1;
            } else {
                $meet->status = 0;
            }
        }

        $meet->save();

        return response()->json([
            'success' => true,
            'meet' => $meet], 200);
    }

    public function addPaymentMethod($id) {

        $meet = Meet::find($id);
        $m = $this->request->all();

        if (!$this->user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit a meet!'
            ], 403);
        }

        if ($meet == NULL) {
            return response()->json([
                'success' => false,
                'message' => 'Meet not found!'
            ], 403);
        }

        $paymentMethod = new MeetPaymentMethod();
        $paymentMethod->meet_id = $id;
        $paymentMethod->payment_type_id = $m['paymentMethodId'];
        $paymentMethod->required = false;
        $paymentMethod->saveOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Added payment method',
            'meet' => $meet], 200);

    }

    public function removePaymentMethod($id) {

        $meet = Meet::find($id);
        $m = $this->request->all();

        if (!$this->user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit a meet!'
            ], 403);
        }

        if ($meet == NULL) {
            return response()->json([
                'success' => false,
                'message' => 'Meet not found!'
            ], 404);
        }

        $paymentMethod = MeetPaymentMethod::where([
            ['meet_id', '=', intval($id)],
            ['payment_type_id', '=', $m['removePaymentMethod']]
            ])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed payment method',
            'meet' => $meet], 200);

    }

    public function updateEvent($id, $eventId) {
        $meet = Meet::find($id);
        $m = $this->request->all();

        if (!$this->user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit a meet!'
            ], 403);
        }

        if ($meet == NULL) {
            return response()->json([
                'success' => false,
                'message' => 'Meet not found!'
            ], 404);
        }

        $meetEvent = MeetEvent::find($eventId);

        if ($meetEvent == NULL) {
            return response()->json([
                'success' => false,
                'message' => 'Meet event not found!'
            ], 404);
        }

        if (array_key_exists('eventName', $m) && $m['eventName'] != '') {
            $meetEvent->eventname = $m['eventName'];
        } else {
            $meetEvent->eventname = null;
        }

        if (array_key_exists('deadline', $m) && $m['deadline'] != '') {
            $meetEvent->deadline = $m['deadline'];
        } else {
            $meetEvent->deadline = null;
        }

        if (array_key_exists('fee', $m) && $m['fee'] != '') {
            $meetEvent->eventfee = $m['fee'];
        } else {
            $meetEvent->eventfee = null;
        }

        if (array_key_exists('fee_non_member', $m) && $m['fee_non_member'] != '') {
            $meetEvent->eventfee_non_member = $m['fee_non_member'];
        } else {
            $meetEvent->eventfee_non_member = null;
        }

        if (array_key_exists('disallowNT', $m) && $m['disallowNT'] != '') {
            $meetEvent->times_required = $m['disallowNT'];
        } else {
            $meetEvent->times_required = false;
        }

        $meetEvent->saveOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Updated Meet Event',
            'meetEvent' => $meetEvent], 200);

    }

    public function addAccess($id) {
        $meet = Meet::find($id);
        $m = $this->request->all();

        if (!$this->user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit a meet!'
            ], 403);
        }

        if ($meet == NULL) {
            return response()->json([
                'success' => false,
                'message' => 'Meet not found!'
            ], 404);
        }

        $memberId = $m['memberId'];

        $access = new MeetAccess();
        $access->meet_id = $meet->id;
        $access->member_id = $memberId;

        try {
            $access->saveOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Meet Access updated.',
                'meetAccess' => $access], 200);
        } catch (Exception $e) {
                return response()->json(['success' => false,
                    'message' => 'Unable to add meet access : ' . $e->getMessage()], 400);
        }

    }

    public function removeAccess($id, $memberId)
    {
        $meet = Meet::find($id);
        $m = $this->request->all();

        if (!$this->user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit a meet!'
            ], 403);
        }

        if ($meet == NULL) {
            return response()->json([
                'success' => false,
                'message' => 'Meet not found!'
            ], 404);
        }

        try {
            MeetAccess::where([
                ['meet_id', '=', intval($id)],
                ['member_id', '=', $memberId]
            ])->delete();

            return response()->json([
                'success' => true,
                'message' => 'Meet Access updated.'
            ], 200);

        } catch (Exception $e) {
            return response()->json(['success' => false,
                'message' => 'Unable to remove meet access : ' . $e->getMessage()], 400);
        }

    }

}