<?php
namespace App\Mail;

use App\MeetEntry;
use App\MeetEntryStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MeetEntryConfirmation extends Mailable {

    use Queueable,
        SerializesModels;

    public $entry;

    public function __construct(MeetEntry $entry)
    {
        $entry->member;
        $entry->member->emergency;
        if ($entry->member->emergency != NULL) {
            $entry->member->emergency->phone;
        }
        $entry->member->phones;
        $entry->member->emails;
        $entry->member->memberships;

        $status = MeetEntryStatus::where('entry_id', '=', $entry->id)
            ->orderBy('id', 'DESC')
            ->first();
        if ($status != NULL) {
            $status->status;
            $entry['status'] = $status;
        }

        if ($entry->disability_s_id != NULL) {
            $entry->disability_s;
        }
        if ($entry->disability_sb_id != NULL) {
            $entry->disability_sb;
        }
        if ($entry->disability_sm_id != NULL) {
            $entry->disability_sm;
        }

        if ($entry->club_id !== NULL) {
            $entry->club;
        }

        $entry->age_group;
        $entry->meet;
        $entry->events;
        $entry->payments;

        foreach($entry->events as $e) {
            $event = $e->event;
        }

        $this->entry = $entry;
    }

    //build the message.
    public function build() {
        Log::debug('build meet-entry-confirmation');
        return $this->view('meet-entry-confirmation');
    }

    public static function convertSeedTime($secTime) {
        if (!strpbrk($secTime, '.')) {

            $secTimeSecs = $secTime;
            $secTimeMs = "00";

        } else {

            list($secTimeSecs, $secTimeMs) = explode('.', $secTime);

            if (strlen($secTimeMs) == 1) {

                $secTimeMs = $secTimeMs . '0';

            }

        }

        $secTimeDisp = floor($secTimeSecs / 60) . ':' . sprintf("%02d", ($secTimeSecs % 60)) . '.' . $secTimeMs;

        if ($secTimeDisp == "0:00.00") {

            $secTimeDisp = "NT";

        }

        return $secTimeDisp;
    }
}
