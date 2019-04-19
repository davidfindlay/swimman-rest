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
		'region'
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