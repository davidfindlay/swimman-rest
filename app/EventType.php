<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventType extends Model
{

	protected $table = 'event_types';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'typename',
		'relay',
		'gender'
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