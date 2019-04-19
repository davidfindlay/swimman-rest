<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EprogramTeam extends Model
{

	protected $table = 'eprogram_events';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'event_id',
		'event_ptr',
		'numheats'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function event() {
		return $this->hasOne('App\MeetEvent', 'id', 'event_id');
	}

	public function meet() {
		return $this->hasOne('App\Meet', 'id', 'meet_id');
	}

}