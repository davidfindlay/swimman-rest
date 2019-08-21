<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{

	protected $table = 'phones';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'phonetype',
		'countrycode',
		'areacode',
		'phonenumber'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['phonetype'];

	// Existing table, no timestamps
	public $timestamps = false;

	public function type() {
	    return $this->hasOne('App\PhoneType', 'phonetype', 'id');
    }

}