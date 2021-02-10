<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordGenerationWord extends Model {
	protected $table = 'password_generation_words';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'word'
    ];

	// Existing table, added timestamps
	public $timestamps = true;
}
