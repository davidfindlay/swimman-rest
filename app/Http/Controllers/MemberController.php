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
            $emails = $member->emails;
			$emergency = $member->emergency;
			$emergencyContact = Phone::find($emergency->phone_id);

			if ($emergencyContact != NULL) {
			    $emergency['phonenumber'] = $emergencyContact->phonenumber;
            }

			foreach($memberships as $m) {
				$club = $m->club;
			}

            $member->club_roles;
            $member->meet_access;

			Log::info($member);

			return response()->json( [
			    'success' => true,
                'member' => $member] );

		} else {
			return response()->json([
			    'success' => false,
				'message' => 'Forbidden to access this user data.'
			], 403);
		}
	}

	public function showOneMemberByNumber($number) {
        $user = $this->request->user();

        if (!$user->is_admin) {
            return response()->json([
                'error' => 'Forbidden to access this user data.'
            ], 403);
        }

        $member = Member::where('number', '=', $number)->first();

        if ($member != NULL) {
            $memberships = $member->memberships;
            $phones = $member->phones;
            $emails = $member->emails;
            $emergency = $member->emergency;
            $emergencyContact = Phone::find($emergency->phone_id);

            if ($emergencyContact != NULL) {
                $emergency['phonenumber'] = $emergencyContact->phonenumber;
            }

            foreach($memberships as $m) {
                $club = $m->club;
            }

            $member->club_roles;
            $member->meet_access;

            return response()->json( [
                'success' => true,
                'member' => $member] );
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Unable to find member.'
            ], 404);
        }

    }

}