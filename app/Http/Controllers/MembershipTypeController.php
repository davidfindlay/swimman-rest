<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

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

    public function isAdmin() {
        if ($this->user && $this->user->is_admin) {
            return true;
        }
        return false;
    }

    public function getMembershipTypes() {
        if ($this->isAdmin()) {

            $membershipTypes = MembershipType::all();

            return response()->json([
                'success' => true,
                'membershipTypes' => $membershipTypes]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden to membership configuration data.'
            ], 403);
        }
    }

    public function createMembershipType() {

    }

    public function editMembershipType() {

    }

}