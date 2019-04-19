<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2/2/19
 * Time: 9:02 AM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
	protected $table = 'member_memberships';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'member_id',
		'club_id',
		'type',
		'status',
		'startdate',
		'enddate',
		'status'
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

}