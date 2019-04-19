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
		$meetEventGroups = $meetDetails->groups;

		$email = $meetDetails->email;
		$phone = $meetDetails->phone;
//		$emailaddress = $email->address;

//		$meetDetails['email'] = $emailaddress;
//		$meetDetails['phone'] = $meetDetails->phone->phonenumber;

		$meetDetails['events'] = $meetEvents;

		foreach ($meetEventGroups as $g) {

			$ruleLink = $g->ruleLink;

			if (isset($ruleLink)) {

				$rule      = $ruleLink->rule;
				$g['rule'] = $rule['rule'];

			}

			$g['events'] = $g->events;

			unset($g['ruleLink']);

		}

		$meetDetails['eventGroups'] = $meetEventGroups;

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