<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeetfeeNonMemberToMeet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet', function (Blueprint $table) {
            $table->float('meetfee_non_member')->nullable();
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
            $table->dropColumn('meetfee_non_member');
        });
    }
}
