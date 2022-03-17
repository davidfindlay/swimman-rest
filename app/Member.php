<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
	protected $table = 'member';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'number',
		'surname',
		'firstname',
		'othernames',
		'dob',
		'gender',
		'address',
		'postal'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = true;

	public function jUsers() {
		return $this->hasMany('App\JUserLink', 'member_id   ', 'id');
	}

	public function memberships() {
		return $this->hasMany('App\Membership', 'member_id', 'id');
	}

	public function club_roles() {
	    return $this->hasMany('App\ClubRole', 'member_id', 'id');
    }

    public function meet_access() {
	    return $this->hasMany('App\MeetAccess', 'member_id', 'id');
    }

	public function phones() {
	    return $this->belongsToMany('App\Phone', 'member_phones')
            ->using('App\MemberPhones')
            ->withPivot([
                'member_id',
                'phone_id'
            ]);
    }

    public function emails() {
        return $this->belongsToMany('App\Email', 'member_emails')
            ->using('App\MemberEmails')
            ->withPivot([
                'member_id',
                'email_id'
            ]);
    }

    public function emergency() {
	    return $this->hasOne('App\MemberEmergencyContact', 'member_id', 'id');
    }
}