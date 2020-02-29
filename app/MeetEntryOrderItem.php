<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEntryOrderItem extends Model
{

	protected $table = 'meet_entry_orders_items';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_entry_orders_id',
		'meet_merchandise_id',
		'qty',
		'price_each_exgst',
        'price_total_exgst',
		'price_total_gst',
		'gst_applied'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table
	public $timestamps = true;

	public function meet() {
		return $this->hasOne('App\Meet', 'id', 'meet_id');
	}

}