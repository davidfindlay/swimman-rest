<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2/2/19
 * Time: 9:02 AM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class MembershipStatus extends Model
{
	protected $table = 'membership_statuses';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'desc',
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

}