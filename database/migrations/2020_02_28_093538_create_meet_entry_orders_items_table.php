<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetEntryOrdersItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meet_entry_orders_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('meet_entry_orders_id');
            $table->bigInteger('meet_merchandise_id');
            $table->integer('qty');
            $table->float('price_each_exgst');
            $table->float('price_total_exgst');
            $table->float('price_total_gst');
            $table->boolean('gst_applied');
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
        Schema::dropIfExists('meet_entry_orders_items');
    }
}
