<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetMerchandise extends Model
{

	protected $table = 'meet_merchandise';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'sku',
		'item_name',
		'description',
        'stock_control',
		'stock',
		'deadline',
		'gst_applicable',
		'exgst',
		'gst',
		'total_price',
		'max_qty',
		'status'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function images()
	{
		return $this->hasMany('App\MeetMerchandiseImage');
	}

	public function meet() {
		return $this->hasOne('App\Meet', 'id', 'meet_id');
	}

}