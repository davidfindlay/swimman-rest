<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberEmergencyContactPhone extends Model
{
	protected $table = 'member_emerg_phones';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'member_emerg_id',
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
}