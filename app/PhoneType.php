<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneType extends Model
{

	protected $table = 'phone_types';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
	    'typename'
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