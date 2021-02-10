<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Meet;
use App\MeetEvent;

use Illuminate\Http\Request;

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
		$meetEvents = $meetDetails->events;

		foreach ($meetEvents as $m) {
		    $eventDistance = $m->distance;
		    $eventDiscipline = $m->discipline;
        }

		$meetEventGroups = $meetDetails->groups;

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
        $meets = Meet::all();
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
        $meet->startdate = $m['startdate'];

        if (array_key_exists('enddate', $m)) {
            $meet->enddate = $m['enddate'];
        }

        if (array_key_exists('contactname', $m)) {
            $meet->contactname = $m['contactname'];
        }

        if (array_key_exists('contactemail', $m)) {
            $meet->contactemail = $m['contactemail'];
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

}