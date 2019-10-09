<?php

namespace App\Listeners;

use Log;
use App\Events\ExampleEvent;
use App\Events\MeetEntryConfirmationEvent;
use App\Mail\MeetEntryConfirmation;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class MeetEntryConfirmationListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MeetEntryConfirmationEvent  $event
     * @return void
     */
    public function handle(MeetEntryConfirmationEvent $entryEvent)
    {
        Log::debug('Attempt to send MeetEntryConfirmationEvent');
        $entry = $entryEvent->entry;
        $to = $entry->member->emails->last()->address;
        Mail::to($to)->send(new MeetEntryConfirmation($entry));
    }
}
