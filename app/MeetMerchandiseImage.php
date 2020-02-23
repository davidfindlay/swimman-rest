<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetMerchandiseImage extends Model
{

	protected $table = 'meet_merchandise_images';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
	    'meet_merchandise_id',
		'meet_id',
		'filename',
		'caption'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	// Existing table, no timestamps
	public $timestamps = false;

	public function meet() {
		return $this->hasOne('App\Meet', 'id', 'meet_id');
	}

    public function meet_merchandise() {
        return $this->hasOne('App\MeetMerchandise', 'id', 'meet_merchandise_id');
    }

}