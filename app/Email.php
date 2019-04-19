<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{

	protected $table = 'emails';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'email_type',
		'address',
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