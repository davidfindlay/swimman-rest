<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class JUser extends Model
{
	protected $table = 'j_users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'username',
		'email',
		'password',
		'usertype',
		'block',
		'sendEmail',
		'registerDate',
		'lastvisitDate',
		'activation',
		'params',
		'lastResetTime',
		'resetCount'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
	];

	// Existing table, no timestamps
	public $timestamps = false;

	public function jUserLinks() {
		return $this->hasMany('App\JUserLink', 'joomla_uid', 'id');
	}
}
