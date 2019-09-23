<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClubRole extends Model
{
	protected $table = 'club_roles';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'member_id',
		'club_id',
		'role_id'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	protected $with = [
	    'role_type'
    ];

	// Existing table, no timestamps
	public $timestamps = false;

	public function club() {
		return $this->hasOne('App\Club', 'id', 'club_id');
	}

	public function member() {
		return $this->hasOne('App\Member', 'id', 'member_id');
	}

    public function role_type() {
        return $this->hasOne('App\ClubRoleType', 'id', 'role_id');
    }

}