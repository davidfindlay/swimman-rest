<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRelayPendingToPaypalPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paypal_payment', function (Blueprint $table) {
            $table->unsignedInteger('relay_pending_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paypal_payment', function (Blueprint $table) {
            Schema::dropIfExists('relay_pending');
        });
    }
}
