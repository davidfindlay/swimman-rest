<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class JUserLink extends Model
{
	protected $table = 'member_msqsite';
	protected $primaryKey = null;
	public $incrementing = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'member_id',
		'joomla_uid',
		'joomla_user'
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

	public function member() {
		return $this->hasOne('App\Member', 'id', 'member_id');
	}

	public function jUser() {
		return $this->hasOne('App\JUser', 'id', 'joomla_uid');
	}
}
