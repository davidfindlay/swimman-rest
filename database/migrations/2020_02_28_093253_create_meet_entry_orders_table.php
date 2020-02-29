<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetEntryOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meet_entry_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('meet_entries_id');
            $table->integer('meet_id');
            $table->integer('member_id');
            $table->float('total_exgst');
            $table->float('total_gst');
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
        Schema::dropIfExists('meet_entry_orders');
    }
}
