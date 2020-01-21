<?php
namespace App\Http\Controllers;

use App\Events\MeetEntryConfirmationEvent;
use App\Jobs\MeetEntryConfirmationEmailJob;
use App\MeetEntryStatus;
use Log;
use App\MeetEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class EmailConfirmationController extends Controller
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

    public function sendMeetEntryConfirmation($meetEntryCode) {

        // For now let people send their own reminders.
//        if (!isset($this->user) || $this->user->is_admin != 1) {
//            Log::error('Attempt to send meet entry confirmation for ' . $meetEntryCode . ' by non-admin', $this->user);
//            return response()->json(['success' => false,
//                'message' => 'You do not have permission to send Meet Entry confirmations.'], 403);
//        }

        $entry = MeetEntry::where('code', '=', $meetEntryCode)->first();

        if (!isset($entry)) {
            return response()->json(['success' => false,
                'message' => 'Meet Entry not found.'], 404);
        }

        Log::info('Meet entry confirmation for meet entry ' . $meetEntryCode . ' sent.');

        dispatch(new MeetEntryConfirmationEmailJob($entry));

        return response()->json(['success' => true,
            'message' => 'Confirmation sent for entry ' . $meetEntryCode,
            'meet_entry' => $entry]);

    }
}