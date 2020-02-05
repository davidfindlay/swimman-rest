<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncludedEventsExtraEventFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet', function (Blueprint $table) {
            $table->integer('included_events')->nullable();
            $table->float('extra_event_fee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meet', function (Blueprint $table) {
            $table->dropColumn('included_events');
            $table->dropColumn('extra_event_fee');
        });
    }
}
