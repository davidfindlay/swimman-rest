<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeetEntryIdToMeetEntriesIncomplete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_entries_incomplete', function (Blueprint $table) {
            $table->integer('meet_entry_id')->nullable();
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
            $table->dropColumn('meet_entry_id');
        });
    }
}
