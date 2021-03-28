<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Club;
use App\Member;
use App\MeetEntry;
use App\MeetEntryIncomplete;
use App\MeetEntryStatus;
use App\Membership;
use Illuminate\Http\Request;

class ClubController extends Controller {

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

	public function getClubs(Request $request)
	{
	    $clubs = Club::where('verified', true)->get();
        return response()->json($clubs);
	}

	public function getMembers($id) {

        $club = Club::find($id);

        // Is this member a club captain for this club
        if (!$this->isAdmin($id)) {
            return response()->json([
                'success' => false,
                'club_id' => $id,
                'club_code' => $club->code,
                'club_name' => $club->clubname,
                'message' => 'You do not have permission to access this clubs\'s member list'
            ], 403);
        }

        $memberships = Membership::where('club_id', '=', $id);

        $members = [];

        foreach ($memberships as $m) {
            $found = false;

            foreach ($members as $mem) {
                if ($mem->id == $m->memberId) {
                    $found = true;
                }
            }

            if ($found) {
                continue;
            }

            $member = Member::find($m->member_id);
            $member->memberships;
            array_push($members, $member);
        }

        return response()->json([
            'success' => true,
            'club' => $club,
            'members' => $members
        ], 200);
    }

    public function getEntries($id) {

//        $memberId = $this->user->member;
//
//        if (isset($memberId)) {
//            $member = Member::find($memberId);
//
//            $member->club_roles;
//
//            return response()->json([
//                'success' => true,
//                'member' => $member
//            ], 200);
//
//        }


        $club = Club::find($id);

        // Is this member a club captain for this club
        if (!$this->isAdmin($id)) {
            return response()->json([
                'success' => false,
                'club_id' => $id,
                'club_code' => $club->code,
                'club_name' => $club->clubname,
                'message' => 'You do not have permission to access this clubs\'s member list'
            ], 403);
        }

        $meetId = $this->request->get('meetId');

        if ($meetId != NULL) {
            $entries = MeetEntry::where('club_id', '=', intval($id))
                ->where('meet_id', '=', intval($meetId))->get();
        } else {
            $entries = MeetEntry::where('club_id', '=', intval($id))->get();
        }

//        $entries = MeetEntry::where('club_id', $id)->get();

        foreach ($entries as $e) {
            $e['meet'] = $e->meet;

            if (isset($e['meet'])) {
                $e['meet']->events;
            }

            foreach ($e->events as $event) {
                $event->event;
            }

            $e->club;
            $e->member;
            $e->age_group;
            $e->lodged_user;
            $e->disability_s;
            $e->disability_sb;
            $e->disability_sm;
            $e->payments;
            $e['emails'] = $e->emails;

            $status = MeetEntryStatus::where('entry_id', '=', $e->id)
                ->orderBy('id', 'DESC')
                ->first();
            if ($status != NULL) {
                $status->status;
                $e['status'] = $status;
            }

            $e['status_history'] = MeetEntryStatus::where('entry_id', '=', $e->id)
                ->orderBy('id', 'DESC')->get();

        }

        return response()->json([
            'success' => true,
            'club_id' => $club->id,
            'club_code' => $club->code,
            'club_name' => $club->clubname,
            'entries' => $entries
        ], 200);

    }

    // Is user admin
    public function isAdmin($clubId) {
        $memberId = $this->user->member;

        if (isset($memberId)) {
            $member = Member::find($memberId);

        } else {
            return false;
        }

        if (isset($member)) {

            foreach ($member->club_roles as $r) {
                if ($r && $r->club_id == $clubId) {
                    return true;
                }
            }
        }

        return false;
    }

}