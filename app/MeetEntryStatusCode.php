<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEntryStatusCode extends Model
{

	protected $table = 'meet_entry_status_codes';
    public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'label',
		'description',
        'cancelled'
	];

}