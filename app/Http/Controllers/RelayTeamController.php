<?php

namespace App\Http\Controllers;

use App\MeetEntry;
use App\MeetEvent;
use App\MeetRelayEntry;
use App\MeetRelayEntryMember;
use App\PasswordGenerationWord;
use App\AgeGroup;
use Illuminate\Http\Request;

use App\Club;
use App\Member;

use DateTime;

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
        $eventId = $this->request->get('eventId');

        if ($eventId != NULL) {
            $relays = MeetRelayEntry::where('club_id', '=', intval($clubId))
                ->where('meetevent_id', '=', intval($eventId))->get();
        } else if ($meetId != NULL) {
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

    public function createRelay() {
        $relay = $this->request->all();

        $clubId = $relay['club_id'];
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

        $relayTeam = new MeetRelayEntry();
        $relayTeamMembers = array();
        $relayTeam->meet_id = $relay['meet_id'];
        $relayTeam->club_id = $relay['club_id'];
        $relayTeam->meetevent_id = $relay['meetevent_id'];

        $relayEvent = MeetEvent::find($relayTeam->meetevent_id);
        $gender = $relayEvent->eventType->gender;

        $ageGroup = NULL;

        // Does the relay have 4 members?
        if (count($relay['members']) == 4) {

            $ageTotal = 0;

            foreach ($relay['members'] as $relayMember) {
                $rm = new MeetRelayEntryMember();
                $rm->member_id = $relayMember['member_id'];
                $rm->leg = $relayMember['leg'];

                $member = Member::find($rm->member_id);

                $todayDt = new DateTime();
                $lastDay = $todayDt->format('y') . '-12-31';

                $dobDT = new DateTime($member->dob);
                $testDateDT = new DateTime($lastDay);

                $ageInt = $dobDT->diff($testDateDT);
                $age = $ageInt->format('%y');
                $ageTotal += $age;

                array_push($relayTeamMembers, $rm);

            }

            $ageGroup = AgeGroup::where('min', '<=', $ageTotal)
                ->where('max', '>=', $ageTotal)
                ->where('swimmers', '=', 4)
                ->where('gender', '=', $gender)
                ->where('age_groups.set', '=', 1)
                ->first();

        } else {
            $ageGroup = AgeGroup::where('min', '=', $relay['agegroup_min'])
                ->where('swimmers', '=', 4)
                ->where('gender', '=', $gender)
                ->where('age_groups.set', '=', 1)
                ->first();

        }

        $relayTeam->agegroup = $ageGroup->id;

        $existingTeams = MeetRelayEntry::where('club_id', '=', $relayTeam->club_id)
            ->where('meetevent_id', '=', $relayTeam->meetevent_id)
            ->where('agegroup', '=', $relayTeam->agegroup)
            ->orderBy('letter', 'ASC')
            ->get();

        if ($relay['letter'] != '') {
            $relayTeam->letter = $relay['letter'];
        } else {
            $relayTeam->letter = $this->getNextLetter($existingTeams);
        }

        $relayTeam->seedtime = $relay['seedtime'];
        $relayTeam->cost = $relayEvent->eventfee;
        $relayTeam->save();

        foreach ($relayTeamMembers as $rtm) {
            $rtm->relay_team = $relayTeam->id;
            $rtm->save();
        }

        return response()->json([
            'success' => true,
            'ageGroup' => $ageGroup,
            'relayTeam' => $relayTeam,
            'relay' => $relay
        ], 200);
    }

    // Get Next letter
    public function getNextLetter($relayTeams) {

        $letter = '';

        $firstLetter = ord("A");
        $letterAvailable = false;
        $curLetter = $firstLetter;

        while($letterAvailable != true) {

            $letter = chr($curLetter);

            // Break out of infinite loop
            if ($letter == "Z") {
                $letterAvailable = true;
            }

            if ($this->checkLetter($relayTeams, $letter) === false) {
                $curLetter++;
            } else {
                $letterAvailable = true;
            }

        }

        return $letter;

    }

    public function checkLetter($relayTeams, $letter) {
        foreach ($relayTeams as $r) {
            if ($r->letter === $letter) {
                return true;
            }
        }

        return false;
    }

}
