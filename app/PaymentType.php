<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{

	protected $table = 'payment_types';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'type_name',
		'logo',
		'warning'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

}