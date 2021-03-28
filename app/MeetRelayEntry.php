<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetRelayEntry extends Model
{

	protected $table = 'meet_entries_relays';
    public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'club_id',
        'meetevent_id',
        'teamname',
		'letter',
        'agegroup',
        'seedtime',
        'cost',
        'paid'
	];

	public function meet() {
	    return $this->hasOne("App\Meet", 'id', 'meet_id');
    }

    public function club() {
	    return $this->hasOne("App\Club", 'id', 'club_id');
    }

    public function event() {
        return $this->hasOne("App\MeetEvent", 'id', 'meetevent_id');
    }

    public function ageGroup() {
	    return $this->hasOne('App\AgeGroup', 'id', 'agegroup');
    }

    public function members() {
	    return $this->hasMany('App\MeetRelayEntryMember', 'relay_team', 'id');
    }

}