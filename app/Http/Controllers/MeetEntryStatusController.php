<?php


namespace App\Http\Controllers;


use App\MeetEntryStatusCode;
use Illuminate\Http\Request;

class MeetEntryStatusController extends Controller {

    private $request;

    /**
     * MemberController constructor.
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function getAll() {
        $statuses = MeetEntryStatusCode::all();

        return response()->json($statuses);
    }

}