<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2/2/19
 * Time: 9:02 AM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class SportsTGMember extends Model
{
	protected $table = 'sportstg_members';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'external_id',
		'number',
		'surname',
		'firstname',
		'member_id',
		'member_data'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

    public function member() {
        return $this->hasOne('App\Member', 'id', 'member_id');
    }

}