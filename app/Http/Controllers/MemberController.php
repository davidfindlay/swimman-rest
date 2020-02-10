<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Email;
use App\Member;

use App\MemberEmails;
use App\Phone;
use App\Club;
use App\Membership;
use App\MembershipType;
use App\MembershipStatus;
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

    public function createMember() {
        if ($this->request->user()->is_admin) {
            return response()->json([
                'error' => 'Forbidden to access create members.'
            ], 403);
        }

        $member = new Member();
        $member->surname = $this->request->surname;
        $member->firstname = $this->request->firstname;
        $member->othernames = "";
        $member->dob = $this->request->dob;

        if ($this->request->number !== NULL && strlen($this->request->number) > 0) {
            // TODO: verify number doesn't exist yet
            $member->number = $this->request->number;
        }

        if ($this->request->gender !== NULL && strlen($this->request->gender) > 0) {
            if (strtoupper($this->request->gender[0]) === 'M') {
                $member->gender = 1;
            } else if (strtoupper($this->request->gender[0]) === 'F') {
                $member->gender = 2;
            } else {
                return response()->json([
                    'error' => 'Gender must be M or F!'
                ], 400);
            }
        } else {
            return response()->json([
                'error' => 'Gender(M or F) is required!'
            ], 400);
        }

        $member->saveOrFail();

        if ($member->number === NULL) {
            $member->number = "X" . $member->id;
            $member->saveOrFail();
        }

        $member->emails()->attach($this->addEmail($this->request->email));
        $member->phones()->attach($this->addPhone($this->request->phone));
        $this->createMembership($member->id,
            $this->request->club,
            $this->request->membershipType,
            $this->request->membershipStatus,
            $this->request->startdate,
            $this->request->enddate);

        $member->emails;
        $member->phones;
        $member->memberships;

        return response()->json([
           'success' => true,
           'member' => $member
        ], 200);

    }

    public function addEmail($email) {

	    $emailObj = new Email();

        $emailObj->email_type = 1;
        $emailObj->address = $email;
        $emailObj->saveOrFail();

        return $emailObj;

    }

    public function addPhone($phone) {
        // TODO: search for existing

        $phoneObj = new Phone();
        $phoneObj->phonetype = 1;
        $phoneObj->phonenumber = $phone;
        $phoneObj->saveOrFail();

        return $phoneObj;
    }

    public function createMembership($memberId, $clubCode, $type, $status, $start, $end) {

	    $club = Club::where('code', '=', $clubCode)->first();

	    $membership = new Membership();
	    $membership->member_id = $memberId;
	    $membership->club_id = $club->id;
	    $membership->type = MembershipType::where('typename', '=', $type)->first()->id;
	    $membership->status = MembershipStatus::where('desc', '=', $status)->first()->id;
	    $membership->startdate = $start;
	    $membership->enddate = $end;
        $membership->activated = 1;

	    $membership->saveOrFail();

	    return $membership;
    }

}