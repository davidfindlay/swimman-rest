<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 1/2/19
 * Time: 7:41 AM
 */

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MeetRuleGroup extends Pivot {
	protected $table = 'meet_rules_groups';
}