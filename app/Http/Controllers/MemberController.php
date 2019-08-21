<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Member;

use App\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller {

	private $request;

	/**
	 * MemberController constructor.
	 */
	public function __construct(Request $request) {
		$this->request = $request;
	}

	public function showOneMember($id)
	{

		$user = $this->request->user();

		Log::info($user->member . " - " . $id);

		if ($user->member == $id) {

			$member = Member::find( $id );
			$memberships = $member->memberships;
			$phones = $member->phones;
			$emergency = $member->emergency;
			$emergencyContact = Phone::find($emergency->phone_id);
			$emergency['phonenumber'] = $emergencyContact->phonenumber;

			foreach($memberships as $m) {
				$club = $m->club;
			}

			Log::info($member);

			return response()->json( $member );

		} else {
			return response()->json([
				'error' => 'Forbidden to access this user data.'
			], 403);
		}
	}

}