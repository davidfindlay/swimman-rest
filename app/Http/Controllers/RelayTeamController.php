<?php

namespace App\Http\Controllers;

use App\MeetEntry;
use App\MeetRelayEntry;
use App\MeetRelayEntryMember;
use Illuminate\Http\Request;

use App\Club;
use App\Member;

class RelayTeamController extends Controller
{

    private $request;
    private $userId;
    private $user;

    /**
     * MemberController constructor.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $user = $this->request->user();
        if ($user != NULL) {
            $this->user = $user;
            $this->userId = intval($user->id);
        } else {
            $this->userId = NULL;
        }
    }

    public function getRelayTeams($clubId) {
        $club = Club::find($clubId);

        // Is this member a club captain for this club
        if (!$this->isAdmin($clubId)) {
            return response()->json([
                'success' => false,
                'club_id' => $clubId,
                'club_code' => $club->code,
                'club_name' => $club->clubname,
                'message' => 'You do not have permission to access this clubs\'s relay teams.'
            ], 403);
        }

        $meetId = $this->request->get('meetId');

        if ($meetId != NULL) {
            $relays = MeetRelayEntry::where('club_id', '=', intval($clubId))
                ->where('meet_id', '=', intval($meetId))->get();
        } else {
            $relays = MeetRelayEntry::where('club_id', '=', intval($clubId))->get();
        }

        foreach ($relays as $r) {
            $members = $r->members;
            $ageGroup = $r->ageGroup;
            foreach ($members as $m) {
                $m->member;
            }
        }

        return response()->json([
            'success' => true,
            'club_id' => $club->id,
            'club_code' => $club->code,
            'club_name' => $club->clubname,
            'relays' => $relays
        ], 200);

    }

    // Is user club admin
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
