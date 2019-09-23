<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIncompleteEntryToPaypalPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('paypal_payment', function (Blueprint $table) {
            // Add meet_entry_incomplete_id column
            $table->integer('meet_entries_incomplete_id')->nullable();
            $table->foreign('meet_entries_incomplete_id')->references('id')
                ->on('meet_entries_incomplete');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('paypal_payment', function (Blueprint $table) {
            // Remove meet_entry_incomplete_id column
            $table->dropForeign('meet_entries_incomplete');
            $table->dropColumn('meet_entries_incomplete_id');
        });
    }
}
