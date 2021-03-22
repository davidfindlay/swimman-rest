<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncompleteEntryIdToMeetEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_entries', function (Blueprint $table) {
            $table->integer('incomplete_entry_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meet_entries', function (Blueprint $table) {
            $table->dropColumn('incomplete_entry_id');
        });
    }
}
