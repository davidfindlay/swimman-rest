<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 11:19 AM
 */

namespace App\Http\Controllers;

use App\Meet;
use App\MeetEvent;
use App\SportsTGMember;
use Illuminate\Http\Request;

class SportsTGController extends Controller {

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

    public function getMembers() {

        if ($this->isAdmin()) {
            $members = SportsTGMember::all();

            return response()->json([
                'success' => true,
                'sportstg_members' => $members]);

        } else {

            return response()->json([
                'success' => false,
                'message' => 'Forbidden to access membership import data.'
            ], 403);

        }

    }

}