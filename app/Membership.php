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

	protected $with = [
	    'membership_type'
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

	public function club() {
		return $this->hasOne('App\Club', 'id', 'club_id');
	}

	public function membership_type() {
	    return $this->hasOne('App\MembershipType', 'id', 'type');
    }
}