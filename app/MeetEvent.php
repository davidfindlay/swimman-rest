<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEvent extends Model
{

	protected $table = 'meet_events';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'type',
        'distance',
        'discipline',
		'legs',
		'eventname',
		'prognumber',
		'progsuffix',
		'eventfee',
		'deadline',
        'session_id',
        'exhibition',
        'freetime',
        'times_required'
	];

	protected $with = [
	    'eventDistance',
        'eventDiscipline',
        'eventType'
    ];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [
	    'distance',
        'discipline',
        'type'
    ];

	// Existing table, no timestamps
	public $timestamps = false;

	public function session() {
	    return $this->hasOne("App\MeetSession", 'id', 'session_id');
    }

    public function eventDistance() {
	    return $this->hasOne("App\EventDistance", 'id', 'distance');
    }

    public function eventDiscipline() {
        return $this->hasOne("App\EventDiscipline", 'id', 'discipline');
    }

    public function eventType() {
        return $this->hasOne("App\EventType", 'id', 'type');
    }

}