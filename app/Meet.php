<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Meet extends Model
{

	protected $table = 'meet';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meetname',
		'startdate',
		'enddate',
		'contactname',
		'contactemail',
		'meetfee',
		'mealfee',
		'location',
		'status',
		'maxevents',
		'mealsincluded',
		'mealname',
		'massagefee',
		'programfee',
        'meetfee_nonmember',
        'minevents',
        'included_events',
        'extra_event_fee',
        'gst_applicable',
        'tax_notes',
        'logged_in_only',
        'guest_ok',
        'non_member_ok'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function events()
	{
		return $this->hasMany('App\MeetEvent')->orderBy('prognumber', 'asc');
	}

	public function sessions()
    {
        return $this->hasMany('App\MeetSession');
    }

	public function groups()
	{
		return $this->hasMany('App\MeetEventGroup');
	}

	public function email() {
		return $this->hasOne('App\Email', 'id', 'contactemail');
	}

	public function phone() {
		return $this->hasOne('App\Phone', 'id', 'contactphone');
	}

	public function paymentTypes() {
	    return $this->hasMany('App\MeetPaymentMethod');
    }

    public function merchandise() {
	    return $this->hasMany('App\MeetMerchandise');
    }

    public function access() {
	    return $this->hasMany('App\MeetAccess');
    }

}