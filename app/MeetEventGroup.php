<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 31/1/19
 * Time: 4:31 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEventGroup extends Model {

	protected $table = 'meet_events_groups';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'max_choices',
		'groupname'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function events() {
		return $this->hasMany('App\MeetEventGroupItem', 'group_id', 'id');
	}

	public function ruleLink() {
		return $this->hasOne('App\MeetRuleGroup', 'meet_events_groups_id', 'id');
	}

}