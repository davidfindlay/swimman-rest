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

		$year = $request->input('year');

		if (isset($year)) {

			$meets = Meet::whereYear('startdate', $year)->get();

			foreach($meets as $m) {
				$email = $m->email;
				$phone = $m->phone;
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
		$emailaddress = $email->address;

		$meetDetails['sessions'] = $meetDetails->sessions;

		if (count($meetDetails->sessions) == 0) {
            $meetDetails['events'] = $meetEvents;
        }

		foreach ($meetEventGroups as $g) {

		    $meetDetails['groups'];


		}


		return response()->json($meetDetails);
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