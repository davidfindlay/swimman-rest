<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RelayPending extends Model
{

	protected $table = 'relay_pending';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'event_id',
		'submited_by_user',
        'relay_data',
        'paid',
        'paypal_payment_id',
        'status'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public $timestamps = true;

}