<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPendingReasonToMeetEntriesIncomplete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet_entries_incomplete', function (Blueprint $table) {
            // Add column pending_reason
            $table->text('pending_reason')->nullable();
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
            // Remove pending reason column
            $table->dropColumn('pending_reason');
        });
    }
}
