<?php

namespace App\Jobs;

use App\MeetEntry;
use App\Member;
use App\MemberEmails;
use App\Mail\MeetEntryConfirmation;
use Illuminate\Support\Facades\Mail;
use Log;

class MeetEntryConfirmationEmailJob extends Job
{
    private $entry;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MeetEntry $entry)
    {
        //
        Log::debug('created job');
        $this->entry = $entry;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug('Attempt to handle MeetEntryConfirmationJob');
        Log::debug($this->entry);
        $member = $this->entry->member;


        if ($member == NULL) {
            Log::error('Unable to find member for entry');
            return;
        }

        $memberEmail = $member->emails->last();
        $to = $memberEmail->address;
        Mail::to($to)->send(new MeetEntryConfirmation($this->entry));
    }
}
