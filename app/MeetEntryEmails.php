<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEntryEmails extends Model
{

	protected $table = 'meet_entry_emails';
    public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_entry_id',
        'timestamp',

	];

}