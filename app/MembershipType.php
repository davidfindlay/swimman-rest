<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2/2/19
 * Time: 9:02 AM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class MembershipType extends Model
{
	protected $table = 'membership_types';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'typename',
		'startdate',
		'enddate',
		'months',
		'weeks',
		'status'
	];

	protected $with = [
	    'membership_status'
    ];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

    public function membership_status() {
        return $this->hasOne('App\MembershipStatus', 'id', 'status');
    }

}