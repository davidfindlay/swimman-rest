<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEntryOrder extends Model
{

	protected $table = 'meet_entry_orders';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_entries_id',
		'meet_id',
		'member_id',
        'total_exgst',
        'total_gst'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = true;

	public function items()
	{
		return $this->hasMany('App\MeetEntryOrderItem', 'meet_entry_orders_id', 'id');
	}

	public function meet() {
		return $this->hasOne('App\Meet', 'id', 'meet_id');
	}

	public function meet_entry() {
	    return $this->hasOne('App\MeetEntry', 'id', 'meet_entries_id');
    }

    public function member() {
        return $this->hasOne('App\Member', 'id', 'member_id');
    }

}