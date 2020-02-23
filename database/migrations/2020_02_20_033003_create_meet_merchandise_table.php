<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetMerchandiseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meet_merchandise', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('meet_id');
            $table->string('sku');
            $table->string('item_name');
            $table->text('description');
            $table->boolean('stock_control');
            $table->float('stock')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->boolean('gst_applicable');
            $table->float('exgst');
            $table->float('gst');
            $table->float('total_price');
            $table->float('max_qty')->nullable();
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
        Schema::dropIfExists('meet_merchandise');
    }
}
