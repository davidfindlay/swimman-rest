<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberEmergencyContact extends Model
{
	protected $table = 'member_emerg';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'member_id',
		'surname',
		'firstname',
        'email_id',
        'phone_id'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function phone() {
	    return $this->hasOne('App\Phone', 'id', 'phone_id');
    }

    public function email() {
        return $this->hasOne('App\Email', 'id', 'email_id');
    }

}