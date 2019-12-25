<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;

class EventMembershipController
{
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

    public function createEventMembership() {
        if ($this->user->is_admin != 1) {
            return response()->json(['success' => false,
                'message' => 'You do not have permission to view Meet Entries.'], 403);
        }

        if ($this->request->member_id != NULL && $this->request->member_id != '') {

        } else {

            // Create member
            $member = Member::create();
            $member->surname = $this->request->surname;
            $member->firstname = $this->request->firstname;
            $member->dob = $this->request->dob;

            if ($this->request->gender === 'Male') {
                $member->gender = 1;
            } elseif ($this->request->gender === 'Female') {
                $member->gender = 2;
            } else {
                // Gender default if not provided
                $member->gender = 1;
            }

            $member->saveOrFail();

            

        }

    }


}