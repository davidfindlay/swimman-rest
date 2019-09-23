<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEntry extends Model
{

	protected $table = 'meet_entries';
    public $timestamps = true;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_id',
		'member_id',
        'age_group_id',
        'meals',
		'medical',
        'cost',
        'notes',
        'club_id',
        'cancelled',
        'massages',
        'programs',
        'lodged_by',
        'disability_status',
        'disability_s_id',
        'disability_sb_id',
        'disability_sm_id',
        'medical_condition',
        'medical_safety',
        'medical_details',
        'code'
	];

	public function meet() {
	    return $this->hasOne("App\Meet", 'id', 'meet_id');
    }

    public function member() {
	    return $this->hasOne("App\Member", 'id', 'member_id');
    }

    public function club() {
        return $this->hasOne("App\Club", 'id', 'club_id');
    }

    public function age_group() {
        return $this->hasOne("App\AgeGroup", 'id', 'age_group_id');
    }

    public function lodged_user() {
        return $this->hasOne("App\User", 'id', 'member_id');
    }

    public function disability_s() {
	    return $this->hasOne('App\DisabilityClassification', 'id', 'disability_s_id');
    }

    public function disability_sb() {
        return $this->hasOne('App\DisabilityClassification', 'id', 'disability_sb_id');
    }

    public function disability_sm() {
        return $this->hasOne('App\DisabilityClassification', 'id', 'disability_sm_id');
    }

    public function status() {
	    return $this->belongsToMany('App\MeetEntryStatusCode', 'meet_entry_statuses', 'code', 'entry_id');
    }

    public function events() {
	    return $this->hasMany('App\MeetEntryEvent', 'meet_entry_id', 'id');
    }

    public function payments() {
	    return $this->hasMany('App\MeetEntryPayment', 'entry_id', 'id');
    }

}