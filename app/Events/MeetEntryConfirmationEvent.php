<?php

namespace App\Events;

use Log;
use App\MeetEntry;
use Illuminate\Queue\SerializesModels;

class MeetEntryConfirmationEvent extends Event
{
    use SerializesModels;

    public $entry;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(MeetEntry $entry)
    {
        $this->entry = $entry;
        Log::debug('Created MeetEntryConfirmationEvent');
    }
}
