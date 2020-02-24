<?php


namespace App\Http\Controllers;

use App\Meet;
use App\MeetAccess;
use App\MeetEvent;
use App\MeetMerchandise;
use App\MeetMerchandiseImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MeetMerchandiseController extends Controller
{

    private $request;
    private $memberId;

    /**
     * MeetMerchandiseController constructor.
     */
    public function __construct(Request $request) {
        $this->request = $request;
        $user = $this->request->user();
        if ($user != NULL) {
            $this->user = $user;
            $this->userId = intval($user->id);
            $this->memberId = $user->member;
        } else {
            $this->userId = NULL;
        }
    }

    public function isAuthorised($meet_id) {
        $user = $this->request->user();
        $isMeetOrganiser = false;

        if ($user->member !== NULL) {

            $meetAccess = MeetAccess::where([['member_id', '=', $this->memberId],
                ['meet_id', '=', $meet_id]])->first();

            if ($meetAccess !== NULL) {
                $isMeetOrganiser = true;
            }

        }

        if (!$isMeetOrganiser) {
            if (!$user->is_admin) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    public function getMerchandiseForMeet($meet_id) {
        $merchandise = MeetMerchandise::where('meet_id', '=', $meet_id);
        $merchandise->images;

        return response()->json([
            'success' => true,
            'meetId' => $meet_id,
            'merchandise' => $merchandise
            ]);
    }

    public function createMerchandiseItem() {

        if (!$this->isAuthorised($this->request->meet_id)) {
            return response()->json([
                'error' => 'You must be a meet organiser for this meet or an admin to create merchandise',
                'meetId' => $this->request->meet_id,
                'user' => $this->request->user()
            ], 403);
        }

        // User must be either admin or meet organiser to get to here
        $merchandise = new MeetMerchandise();
        $merchandise->meet_id = $this->request->meet_id;
        $merchandise->sku = $this->request->sku;
        $merchandise->item_name = $this->request->item_name;
        $merchandise->description = $this->request->description;
        $merchandise->stock_control = $this->request->stock_control;
        $merchandise->stock = $this->request->stock;
        $merchandise->deadline = $this->request->deadline;
        $merchandise->gst_applicable = $this->request->gst_applicable;
        $merchandise->exgst = $this->request->exgst;
        $merchandise->gst = $this->request->gst;
        $merchandise->total_price = $this->request->total_price;
        $merchandise->max_qty = $this->request->max_qty;
        $merchandise->status = $this->request->status;
        $merchandise->saveOrFail();

        return response()->json([
            'success' => true,
            'meetId' => $merchandise->meet_id,
            'merchandise' => $merchandise
        ]);

    }

    public function updateMerchandiseItem($merchandiseId) {

        if (!$this->isAuthorised($this->request->meet_id)) {
            return response()->json([
                'error' => 'You must be a meet organiser for this meet or an admin to update meet merchandise',
                'meetId' => $this->request->meet_id,
                'user' => $this->request->user
            ], 403);
        }

        // User must be either admin or meet organiser to get to here
        $merchandise = MeetMerchandise::find($merchandiseId);
        $merchandise->sku = $this->request->sku;
        $merchandise->item_name = $this->request->item_name;
        $merchandise->description = $this->request->description;
        $merchandise->stock_control = $this->request->stock_control;
        $merchandise->stock = $this->request->stock;
        $merchandise->deadline = $this->request->deadline;
        $merchandise->gst_applicable = $this->request->gst_applicable;
        $merchandise->exgst = $this->request->exgst;
        $merchandise->gst = $this->request->gst;
        $merchandise->total_price = $this->request->total_price;
        $merchandise->max_qty = $this->request->max_qty;
        $merchandise->status = $this->request->status;
        $merchandise->saveOrFail();

        return response()->json([
            'success' => true,
            'meetId' => $merchandise->meet_id,
            'merchandise' => $merchandise
        ]);
    }

    public function getMerchandiseItem($merchandiseId) {
        $merchandise = MeetMerchandise::find($merchandiseId);
        $merchandise->images;

        return response()->json(['success' => true,
            'merchandise_id' => $merchandiseId,
            'merchandise_item' => $merchandise]);
    }

    public function addMerchandiseImage($merchandiseId) {
        $imageData = json_decode($this->request->input('imageData'));
        $merchandise = MeetMerchandise::find($merchandiseId);

        if ($merchandise === NULL) {
            return response()->json([
                'error' => 'Unable to find merchandise item: ' . $merchandiseId,
                'imageData' => $imageData,
                'user' => $this->request->user
            ], 403);
        }

        $meet_id = $merchandise->meet_id;

        if (!$this->isAuthorised($meet_id)) {
            return response()->json([
                'error' => 'You must be a meet organiser for this meet or an admin to add an image to merchandise',
                'imageData' => $imageData,
                'user' => $this->request->user
            ], 403);
        }

        $picName = $this->request->file('image')->getClientOriginalName();
        $path = $_SERVER['DOCUMENT_ROOT'] . '/meets/' . $meet_id . '/merchandise/';
        File::makeDirectory($path, 0777, true, true);
        $this->request->file('image')->move($path, $picName);

        $merchandise = MeetMerchandise::find($merchandiseId);
        $merchandiseImage = new MeetMerchandiseImage();
        $merchandiseImage->meet_merchandise_id = $merchandiseId;
        $merchandiseImage->meet_id = $merchandise->meet_id;
        $merchandiseImage->filename = $picName;
        $merchandiseImage->caption = $imageData->caption;
        $merchandiseImage->saveOrFail();

        return response()->json([
            'success' => true,
            'meetId' => $merchandise->meet_id,
            'merchandiseImage' => $merchandiseImage
        ]);
    }

    public function deleteMerchandiseItem($merchandiseId) {
        $merchandise = MeetMerchandise::find($merchandiseId);
        $meet_id = $merchandise->meet_id;
        if (!$this->isAuthorised($meet_id)) {
            return response()->json([
                'error' => 'You must be a meet organiser for this meet or an admin to delete merchandise',
                'user' => $this->request->user
            ], 403);
        }

        // TODO: Add code to prevent deletion if the item has orders

        $merchandise->delete();

        return response()->json([
            'success' => true
        ]);
    }
}