<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetRelayEntryMember extends Model
{

	protected $table = 'meet_entries_relays_members';
    public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'relay_team',
		'member_id',
        'leg'
	];

	public function team() {
	    return $this->hasOne("App\MeetRelayEntry", 'id', 'relay_team');
    }

    public function member() {
	    return $this->hasOne("App\Member", 'id', 'member_id');
    }

}