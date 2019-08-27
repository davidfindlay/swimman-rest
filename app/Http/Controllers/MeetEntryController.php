<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\MeetEntry;
use App\MeetEntryEvent;
use App\MeetEntryIncomplete;
use App\MeetEntryStatusCode;
use App\Member;
use App\Club;
use App\AgeGroup;

use Illuminate\Http\Request;

class MeetEntryController extends Controller {

    private $request;
    private $userId;

    /**
     * MemberController constructor.
     */
    public function __construct(Request $request) {
        $this->request = $request;
        $user = $this->request->user();
        if ($user != NULL) {
            $this->userId = intval($user->id);
        } else {
            $this->userId = NULL;
        }
    }

    public function createIncompleteEntry() {

//        if ($this->userId != NULL && $this->userId != intval($this->request->all()['user_id'])) {
//            return response()->json(['error' => "You cannot submit an entry for another user!"],403);
//        }

        $entry = $this->request->all();
//        if ($entry->entrydata != NULL) {
//            $meetId = $entry->entrydata->meetId;
//            if ($meetId != NULL) {
//                $entry['meet_id'] = $meetId;
//            }
//        }

        if ($this->userId != NULL) {
            $entry['user_id'] = $this->userId;

            if ($this->request->user()->member != NULL) {
                $entry['member_id'] = $this->request->user()->member;
            }
        }

        $entryData = json_encode($entry['entrydata']);
        $entry['entrydata'] = $entryData;

        $statusCode = MeetEntryStatusCode::where('label', '=', 'Incomplete')->first()->id;
        $entry['status_id'] = $statusCode;

        $entryObj = MeetEntryIncomplete::create($entry);
        return response()->json($entryObj);
    }

    public function updateIncompleteEntry($id) {
        $entry = MeetEntryIncomplete::find($id);

        if ($this->userId != intval($entry->user_id)) {
            return response()->json(['error' => "You cannot edit an entry for another user "],403);
        }

        $entry->entrydata = json_encode($this->request->input('entrydata'));
        $entry->save();

        return response()->json($entry);
    }
    public function deleteIncompleteEntry($id) {
        $entry = MeetEntryIncomplete::find($id);

        if ($this->userId != intval($entry->user_id)) {
            return response()->json(['error' => "You cannot edit an entry for another user "],403);
        }

        $entry->delete();

        return response()->json('Removed successfully.');
    }

    public function getIncompleteEntry($id) {
        $entry = MeetEntryIncomplete::find($id);

        if ($this->userId != intval($entry->user_id)) {
            return response()->json(['error' => "You cannot get an entry for another user "],403);
        }

        return response()->json($entry);
    }

    public function index() {
        $entry = MeetEntryIncomplete::where('user_id', '=', $this->userId);
        return response()->json($entry);
    }

    public function finaliseIncompleteEntry($id) {
        $entry = MeetEntryIncomplete::find($id);

        $entryData = json_decode($entry['entrydata']);

        if ($entryData->membershipDetails != null) {

            $membershipDetails = $entryData->membershipDetails;

            // User isn't an MSA member so don't try to create an entry for them
            if ($membershipDetails->member_type != 'msa') {
                return response()->json();
            }

            if ($membershipDetails->member_number == '') {
                return response()->json();
            }

            $member = Member::where('number', '=', $membershipDetails->member_number)->first();
            $ageUpDate = date('Y') . "-12-31";
            $age = date_diff(date_create($member->dob), date_create($ageUpDate))->format('%y');
            $gender = $member->gender;

//            return response()->json(['age' => $age,
//                'gender' => $gender,
//                'ageUpDate' => $ageUpDate,
//                'dob' => $member->dob]);

            $ageGroup = AgeGroup::where([['set', '=', 1],
                ['min', '<=', $age],
                ['max', '>=', $age],
                ['gender', '=', $gender],
                ['swimmers', '=', 1]])->first();

            $meetEntry = new MeetEntry();
            $meetEntry->meet_id = $entryData->meetId;
            $meetEntry->member_id = $member->id;
            $meetEntry->age_group_id = $ageGroup->id;
            $meetEntry->meals = 0;
            $meetEntry->cancelled = 0;

            // Get club
            if ($membershipDetails->club_selector != "") {
                $meetEntry->club_id = $membershipDetails->club_selector;
            } else {
                $club = Club::where('code', '=', $membershipDetails->club_code)->first();
                $meetEntry->club_id = $club->id;
            }

            $meetEntry->saveOrFail();
            $meetEntryId = $meetEntry->id;

            // Add events
            foreach ($entryData->entryEvents as $eventEntry) {
                $meetEntryEvent = new MeetEntryEvent();
                $meetEntryEvent->meet_entry_id = $meetEntryId;
                $meetEntryEvent->member_id = $member->id;
                $meetEntryEvent->event_id = $eventEntry->event_id;
                $meetEntryEvent->seedtime = $eventEntry->seedtime;
                $meetEntryEvent->saveOrFail();
            }

            $meetEntryCreated = MeetEntry::find($meetEntryId);
            $meetEntryCreated['events'] = MeetEntryEvent::where('meet_entry_id', '=', $meetEntryId);

            return response()->json(['incomplete_entry' => $entry,
                'meet_entry' => $meetEntryCreated], 200);

        }


        $meetEntry = new MeetEntry;


        return response()->json(['error' => 'unable to create entry', 'incomplete_entry' => $entry], 400);
    }

}