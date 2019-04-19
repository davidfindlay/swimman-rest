<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EprogramTeam extends Model
{

	protected $table = 'eprogram_entry';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'event_ptr',
		'ath_no',
		'heatnumber',
        'lanenumber',
        'seedtime',
        'heatplace',
        'finalplace',
        'finaltime',
        'ev_score'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function eprogram_event() {
		return $this->hasOne('App\EprogramEvent', 'id', 'event_ptr');
	}

	public function meet() {
		return $this->hasOne('App\Meet', 'id', 'meet_id');
	}

}