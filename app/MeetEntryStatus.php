<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MeetEntryStatus extends Model
{
    protected $table = 'meet_entry_statuses';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entry_id',
        'code',
        'changed'
    ];

    protected $with = [
        'status'
    ];

    public function status() {
        return $this->hasOne('App\MeetEntryStatusCode', 'id', 'code');
    }

    public function scopeIdDescending($query)
    {
        return $query->orderBy('id','DESC');
    }


}