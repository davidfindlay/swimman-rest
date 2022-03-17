<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 2/2/19
 * Time: 9:02 AM
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

class MembershipImport extends Model
{
	protected $table = 'membership_imports';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'requested_at',
		'started_at',
		'finished_at',
		'source',
		'filename',
		'checksum',
        'user_id',
        'members',
        'processed',
        'members_new',
        'members_updated',
        'members_renewed'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [];

	protected $with = [
	    'user',
        'logs'
    ];

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function logs() {
        return $this->hasMany('App\MembershipImportLog', 'import_id', 'id');
    }

}