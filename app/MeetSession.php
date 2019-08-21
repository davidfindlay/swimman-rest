<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetSession extends Model
{

    protected $table = 'meet_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'meet_id',
        'enddate',
        'location',
        'session_name',
        'session_date',
        'warmup',
        'start_time'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    // Existing table, no timestamps
    public $timestamps = false;

    public function meet()
    {
        return $this->hasOne('App\Meet', 'id', 'meet_id');
    }

    public function events()
    {
        return $this->hasMany('App\MeetEvent', 'session_id', 'id');
    }


}