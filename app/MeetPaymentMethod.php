<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetPaymentMethod extends Model
{

	protected $table = 'meet_payment_methods';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'payment_type_id',
		'required'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function meet()
	{
		return $this->hasOne('App\Meet', 'id', 'meet_id');
	}

	public function paymentType() {
	    return $this->hasOne('App\PaymentType', 'id', 'payment_type_id');
    }

}