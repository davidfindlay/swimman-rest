<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 1/2/19
 * Time: 7:36 AM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEventGroupItem extends Model {

	protected $table = 'meet_events_groups_items';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'group_id',
		'event_id',
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