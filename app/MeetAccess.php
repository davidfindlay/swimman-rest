<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetAccess extends Model
{

	protected $table = 'meet_access';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'member_id',
		'juser'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function meet()
	{
		return $this->hasOne('App\Meet', 'id', 'meet_id');
	}

	public function member()
    {
        return $this->hasOne('App\Member', 'id', 'member_id');
    }

}