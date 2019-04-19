<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEvent extends Model
{

	protected $table = 'meet_events';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'type',
		'discipline',
		'legs',
		'distance',
		'eventname',
		'prognumber',
		'progsuffix',
		'eventfee',
		'deadline'
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