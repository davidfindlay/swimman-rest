<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RelayPending extends Model
{

	protected $table = 'relays_pending';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'event_id',
		'submited_by_user',
        'entrydata',
        'paid',
        'status'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	public $timestamps = true;

}