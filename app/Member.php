<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
	protected $table = 'member';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'number',
		'surname',
		'firstname',
		'othernames',
		'dob',
		'gender',
		'address',
		'postal'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function jUsers() {
		return $this->hasMany('App\JUserLink', 'member_id', 'id');
	}

	public function memberships() {
		return $this->hasMany('App\Membership', 'member_id', 'id');
	}
}