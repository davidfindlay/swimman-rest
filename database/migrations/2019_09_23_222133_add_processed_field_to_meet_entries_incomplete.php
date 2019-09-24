<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProcessedFieldToMeetEntriesIncomplete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_entries_incomplete', function (Blueprint $table) {
            $table->timestamp('finalised_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meet_entries_incomplete', function (Blueprint $table) {
            $table->dropColumn('finalised_at');
        });
    }
}
