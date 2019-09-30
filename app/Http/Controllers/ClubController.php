<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Club;

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
        // Is this member a club captain for this club


        $club = Club::find($id);
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

}