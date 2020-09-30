<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSeedtimeSuggestionsToMeetevent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_events', function (Blueprint $table) {
            $table->boolean('disable_seedtime_suggestions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meet_events', function (Blueprint $table) {
            $table->boolean('disable_seedtime_suggestions')->nullable();
        });
    }
}
