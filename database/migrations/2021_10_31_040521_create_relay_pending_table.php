<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelayPendingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relay_pending', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('meet_id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('submitted_by_user')->nullable();
            $table->longText('relay_data');
            $table->float('paid');
            $table->unsignedInteger('paypal_payment_id');
            $table->unsignedSmallInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('relay_pending');
    }
}
