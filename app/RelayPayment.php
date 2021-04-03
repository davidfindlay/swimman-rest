<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RelayPayment extends Model
{

	protected $table = 'relay_payments';
    public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'club_id',
        'qty',
        'amount',
		'event_id',
        'datetime',
        'method'
	];

	public function meet() {
	    return $this->hasOne("App\Meet", 'id', 'meet_id');
    }

    public function club() {
        return $this->hasOne("App\Club", 'id', 'club_id');
    }
}