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

class MembershipTypeController extends Controller
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

    public function getMembershipTypes($id) {

    }

    public function createMembershipType() {

    }

    public function editMembershipType() {

    }

}