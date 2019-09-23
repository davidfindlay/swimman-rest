<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DisabilityClassification extends Model
{

	protected $table = 'disability_classifications';
    public $timestamps = true;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'classification'
	];

	public function event_disciplines() {
	    return $this->belongsToMany('App\EventDiscipline', 'disability_classifications_event_disciplines', 'event_disciplines_id')
            ->using('App\DisabilityClassificationEventDiscipline')
            ->withPivot([
                'disability_classifications_id',
                'event_disciplines_id'
            ]);
    }

}