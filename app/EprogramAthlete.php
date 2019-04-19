<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EprogramTeam extends Model
{

	protected $table = 'eprogram_athletes';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'member_id',
		'ath_no',
		'team_no',
		'firstname',
        'surname',
        'dob',
        'msanumber',
        'gender',
        'age'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function member() {
		return $this->hasOne('App\Member', 'id', 'member_id');
	}

	public function meet() {
		return $this->hasOne('App\Meet', 'id', 'meet_id');
	}

}