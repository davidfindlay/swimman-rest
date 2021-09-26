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
use DB;

class MemberController extends Controller {

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

	public function isAdmin() {
	    if ($this->user && $this->user->is_admin) {
	        return true;
        }
	    return false;
    }

	public function showOneMember($id) {

		$user = $this->request->user();

		Log::info($user->member . " - " . $id);

		if ($user->member == $id || $this->isAdmin()) {

			$member = Member::find( $id );
			$memberships = $member->memberships;
			$phones = $member->phones;
            $emails = $member->emails;
			$emergency = $member->emergency;
			if ($emergency != null) {
                $emergencyContact = Phone::find($emergency->phone_id);

                if ($emergencyContact != NULL) {
                    $emergency['phonenumber'] = $emergencyContact->phonenumber;
                }
            }

			foreach($memberships as $m) {
				$club = $m->club;
			}

            $club_roles = $member->club_roles;
			foreach ($club_roles as $m) {
                $club = $m->club;
            }

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
            if ($emergency !== NULL) {
                $emergencyContact = Phone::find($emergency->phone_id);
                if ($emergencyContact != NULL) {
                    $emergency['phonenumber'] = $emergencyContact->phonenumber;
                }
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
        $user = $this->request->user();

        if (!$user->is_admin) {
            return response()->json([
                'error' => 'Forbidden to access create members.',
                'user' => $user
            ], 403);
        }

        $member = new Member();
        $member->surname = $this->titleCase($this->request->surname);
        $member->firstname = $this->titleCase($this->request->firstname);
        $member->othernames = "";

        // Handle date format with slashes
        if (strpos($this->request->dob, '/' ) !== false) {
            $dateComponents = explode('/', $this->request->dob);
            if (count($dateComponents) < 3) {
                return response()->json([
                    'error' => 'Date format invalid!'
                ], 400);
            }
            $member->dob = $dateComponents[2] . '-' . $dateComponents[1] . '-' . $dateComponents[0];
        } else {
            $member->dob = $this->request->dob;
        }

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

    function titleCase($string)
    {
        $word_splitters = array(' ', '-', "O'", "L'", "D'", 'St.', 'Mc');
        $lowercase_exceptions = array('the', 'van', 'den', 'von', 'und', 'der', 'de', 'da', 'of', 'and', "l'", "d'");
        $uppercase_exceptions = array('III', 'IV', 'VI', 'VII', 'VIII', 'IX');

        $string = strtolower($string);
        foreach ($word_splitters as $delimiter)
        {
            $words = explode($delimiter, $string);
            $newwords = array();
            foreach ($words as $word)
            {
                if (in_array(strtoupper($word), $uppercase_exceptions))
                    $word = strtoupper($word);
                else
                    if (!in_array($word, $lowercase_exceptions))
                        $word = ucfirst($word);

                $newwords[] = $word;
            }

            if (in_array(strtolower($delimiter), $lowercase_exceptions))
                $delimiter = strtolower($delimiter);

            $string = join($delimiter, $newwords);
        }
        return $string;
    }

    public function findMember() {
        $user = $this->request->user();

        if (!$user->is_admin) {
            return response()->json([
                'error' => 'Forbidden to search members.',
                'user' => $user
            ], 403);
        }

        $term = '%' . trim($this->request['term']) . '%';

//        $results = DB::select("SELECT t.* FROM member t WHERE number LIKE '%:term%' OR CONCAT(surname, ' ', firstname) LIKE '%:term%' OR CONCAT(firstname, ' ', surname) LIKE '%:term%';", ['term' => trim($term)]);

        $results = Member::whereRaw('number LIKE ? OR CONCAT(surname, \' \', firstname) LIKE ? OR CONCAT(firstname,  \' \', surname) LIKE ?',
            array($term, $term, $term))
            ->with(['memberships'])
            ->get();

        return response()->json([
            'success' => true,
            'term' => $term,
            'results' => $results
        ], 200);
    }

}