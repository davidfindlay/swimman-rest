<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PayPalPayment extends Model
{

	protected $table = 'paypal_payment';
    public $timestamps = true;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'meet_entry_id',
		'invoice_id',
        'payer_name',
        'payer_email',
		'paid',
        'meet_club_payment_id',
        'meet_entries_incomplete_id'
	];

	public function meet_entry() {
	    return $this->hasOne("App\MeetEntry", 'id', 'meet_entry_id');
    }


}