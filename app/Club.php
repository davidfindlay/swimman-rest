<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2/2/19
 * Time: 9:02 AM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
	protected $table = 'clubs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'code',
		'clubname',
		'postal',
		'region',
        'verified'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
    public $timestamps = false;

    public function memberships() {
        return $this->hasMany('App\Membership', 'club_id', 'id');
    }

    public function roles() {
        return $this->hasMany('App\ClubRole', 'club_id', 'id');
    }

    public function branchRegion() {
        return $this->hasOne('App\BranchRegion', 'id', 'region');
    }

}