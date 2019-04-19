<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 1/2/19
 * Time: 7:41 AM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetRuleGroup extends Model {

	protected $table = 'meet_rules_groups';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'rule_id',
		'meet_events_groups_id',
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function rule() {
		return $this->hasOne('App\MeetRule', 'id', 'rule_id');
	}

}