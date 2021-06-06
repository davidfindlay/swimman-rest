<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEntryEvent extends Model
{

	protected $table = 'meet_events_entries';
    public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_entry_id',
		'event_id',
        'member_id',
        'relay_id',
		'leg',
        'seedtime',
        'cost',
        'paid',
        'cancelled',
        'scratched',
        'status_code'
	];

	public function meet_entry() {
	    return $this->hasOne("App\MeetEntry", 'id', 'meet_entry_id');
    }

    public function event() {
	    return $this->hasOne("App\MeetEvent", 'id', 'event_id');
    }

    public function member() {
        return $this->hasOne("App\Member", 'id', 'member_id');
    }

    public function relay() {
        return $this->hasOne("App\RelayEntry", 'id', 'relay_id');
    }

    public function status() {
        return $this->hasOne("App\MeetEntryStatusCode", 'id', 'status_code');
    }

}