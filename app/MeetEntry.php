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
        'disability_s',
        'disability_sb',
        'disability_sm',
        'medical_condition',
        'medical_safety',
        'medical_details'
	];

	public function meet() {
	    return $this->hasOne("App\Meet", 'id', 'meet_id');
    }

    public function member() {
	    return $this->hasOne("App\Member", 'id', 'member_id');
    }

    public function age_group() {
        return $this->hasOne("App\AgeGroup", 'id', 'age_group_id');
    }

    public function lodged_user() {
        return $this->hasOne("App\User", 'id', 'member_id');
    }

}