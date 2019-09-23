<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEntryPayment extends Model
{

	protected $table = 'meet_entry_payments';
    public $timestamps = true;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'entry_id',
		'member_id',
        'received',
        'amount',
		'method',
        'comment',
        'club_id'
	];

	public function entry() {
	    return $this->hasOne("App\MeetEntry", 'id', 'entry_id');
    }

    public function member() {
        return $this->hasOne("App\Member", 'id', 'member_id');
    }

    public function club() {
        return $this->hasOne("App\Club", 'id', 'club_id');
    }
}