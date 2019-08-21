<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventDistance extends Model
{

	protected $table = 'event_distances';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'distance',
		'splits',
		'metres',
		'course'
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