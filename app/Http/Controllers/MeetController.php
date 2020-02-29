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

}