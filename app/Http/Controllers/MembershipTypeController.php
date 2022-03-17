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

            $membershipTypes = MembershipType::orderBy('id', 'desc')->get();

            return response()->json([
                'success' => true,
                'membershipTypes' => $membershipTypes]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden to access membership configuration data.'
            ], 403);
        }
    }

    public function createMembershipType() {
        if ($this->isAdmin()) {
            $newMembershipData = $this->request->all();
            $membershipType = new MembershipType();

            $membershipType = $this->setMembershipTypeValues($membershipType, $newMembershipData);

            $membershipType->saveOrFail();

            return response()->json([
                'success' => true,
                'membershipType' => $membershipType]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden to access membership configuration data.'
            ], 403);
        }
    }

    public function editMembershipType($id) {
        if ($this->isAdmin()) {
            $membershipType = MembershipType::find($id);

            $newMembershipData = $this->request->all();
            $membershipType = $this->setMembershipTypeValues($membershipType, $newMembershipData);

            $membershipType->saveOrFail();
            return response()->json([
                'success' => true,
                'membershipType' => $membershipType]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden to access membership configuration data.'
            ], 403);
        }
    }

    public function setMembershipTypeValues($membershipType, $newMembershipData) {

        $membershipType->typename = $newMembershipData['typename'];

        if (array_key_exists('startdate', $newMembershipData)) {
            if ($newMembershipData['startdate'] == '') {
                $membershipType->startdate = NULL;
            } else {
                $membershipType->startdate = $newMembershipData['startdate'];
            }
        } else {
            $membershipType->startdate = NULL;
        }

        if (array_key_exists('enddate', $newMembershipData)) {
            if ($newMembershipData['enddate'] == '') {
                $membershipType->enddate = NULL;
            } else {
                $membershipType->enddate = $newMembershipData['enddate'];
            }
        } else {
            $membershipType->enddate = NULL;
        }

        if (array_key_exists('months', $newMembershipData)) {
            if ($newMembershipData['months'] == '') {
                $membershipType->months = NULL;
            } else {
                $membershipType->months = $newMembershipData['months'];
            }

        } else {
            $membershipType->months = NULL;
        }

        if (array_key_exists('weeks', $newMembershipData)) {
            if ($newMembershipData['weeks'] == '') {
                $membershipType->weeks = NULL;
            } else {
                $membershipType->weeks = $newMembershipData['weeks'];
            }
        } else {
            $membershipType->weeks = NULL;
        }

        if (array_key_exists('status', $newMembershipData)) {
            if ($newMembershipData['status'] == '') {
                $membershipType->status = NULL;
            } else {
                $membershipType->status = $newMembershipData['status'];
            }

        } else {
            $membershipType->status = NULL;
        }

        if (array_key_exists('active', $newMembershipData)) {
            if ($newMembershipData['active'] == '') {
                $membershipType->active = NULL;
            } else {
                $membershipType->active = $newMembershipData['active'];
            }
        } else {
            $membershipType->active = NULL;
        }

        return $membershipType;

    }

}