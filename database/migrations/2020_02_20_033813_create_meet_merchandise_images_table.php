<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeetMerchandiseImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meet_merchandise_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('meet_merchandise_id');
            $table->integer('meet_id');
            $table->string('filename');
            $table->text('caption');
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
        Schema::dropIfExists('meet_merchandise_images');
    }
}
