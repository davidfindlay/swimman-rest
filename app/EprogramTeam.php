<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EprogramTeam extends Model
{

	protected $table = 'eprogram_teams';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'club_id',
		'team_no',
		'clubcode',
		'clubname',
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function club() {
		return $this->hasOne('App\Club', 'id', 'club_id');
	}

	public function meet() {
		return $this->hasOne('App\Meet', 'id', 'meet_id');
	}

}