<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AgeGroup extends Model
{

	protected $table = 'age_groups';
    public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'set',
		'min',
        'max',
        'gender',
		'groupname',
        'swimmers'
	];

}