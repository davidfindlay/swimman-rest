<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEntryIncomplete extends Model
{

	protected $table = 'meet_entries_incomplete';
    public $timestamps = true;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'user_id',
        'status_id',
        'member_id',
		'entrydata',
        'pending_reason',
        'code',
        'finalised_at'
	];

	public function meet() {
	    return $this->hasOne("App\Meet", 'id', 'meet_id');
    }

    public function user() {
	    return $this->hasOne("App\User", 'id', 'user_id');
    }

    public function status() {
        return $this->hasOne("App\MeetEntryStatusCode", 'id', 'status_id');
    }

    public function member() {
        return $this->hasOne("App\Member", 'id', 'member_id');
    }

}