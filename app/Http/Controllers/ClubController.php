<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Club;
use App\ClubRole;
use App\Member;
use App\MeetEntry;
use App\MeetEntryIncomplete;
use App\MeetEntryStatus;
use App\Membership;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

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

	public function getClubs()
	{
	    $clubs = Club::where('verified', true)->get();
        return response()->json($clubs);
	}

	public function getAllClubs() {
        $clubs = Club::with(['memberships', 'roles', 'branchRegion'])
            ->get();
        return response()->json([
            'success' => true,
            'clubs' => $clubs
        ], 200);
    }

    public function getSingleClub($id) {
        $clubs = Club::with(['memberships', 'roles.member', 'roles', 'branchRegion'])
            ->find($id);
        return response()->json([
            'success' => true,
            'club' => $clubs
        ], 200);
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

    public function updateClub($clubId) {
        $club = Club::find($clubId);
        $c = $this->request->all();

        if (!$this->user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit a club!'
            ], 403);
        }

        $club->clubname = $c['clubname'];
        $club->code = $c['clubcode'];
        $club->verified = $c['verified'];

        switch ($c['region']) {
            case 'south':
                $club->region = 1;
                break;
            case 'sunshine':
                $club->region = 2;
                break;
            case 'central':
                $club->region = 3;
                break;
            case 'north':
                $club->region = 4;
                break;
            default:
                $club->region = NULL;
        }

        if ($club->save()) {
            $club = Club::find($clubId);

            return response()->json([
                'success' => true,
                'club_id' => $clubId,
                'club' => $club
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update club details'
            ], 400);
        }
    }

    public function addAccess($id) {
        $club = Club::find($id);
        $c = $this->request->all();

        if (!$this->user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update club access!'
            ], 403);
        }

        if ($club == NULL) {
            return response()->json([
                'success' => false,
                'message' => 'Club not found!'
            ], 404);
        }

        $memberId = $c['member_id'];

        $access = new ClubRole();
        $access->club_id = $club->id;
        $access->member_id = $memberId;
        $access->role_id = 1;

        try {
            $access->saveOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Club Access updated.',
                'meetAccess' => $access], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false,
                'message' => 'Unable to add club access : ' . $e->getMessage()], 400);
        }

    }

    public function removeAccess($id, $memberId) {
        $club = Club::find($id);
        $m = $this->request->all();

        if (!$this->user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update club access!'
            ], 403);
        }

        if ($club == NULL) {
            return response()->json([
                'success' => false,
                'message' => 'Club not found!'
            ], 404);
        }

        try {
            ClubRole::where([
                ['club_id', '=', intval($id)],
                ['member_id', '=', $memberId]
            ])->delete();

            return response()->json([
                'success' => true,
                'message' => 'Club Access updated.'
            ], 200);

        } catch (Exception $e) {
            return response()->json(['success' => false,
                'message' => 'Unable to remove club access : ' . $e->getMessage()], 400);
        }

    }

}