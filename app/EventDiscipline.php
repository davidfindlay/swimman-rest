<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventDiscipline extends Model
{

	protected $table = 'event_disciplines';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'discipline',
        'abrev'
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