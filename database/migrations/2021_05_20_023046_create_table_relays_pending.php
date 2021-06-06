<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRelaysPending extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_relays_pending', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('meet_id');
            $table->integer('event_id');
            $table->integer('submited_by_user');
            $table->longText('entrydata');
            $table->float('paid');
            $table->integer('status');
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
        Schema::dropIfExists('table_relays_pending');
    }
}
