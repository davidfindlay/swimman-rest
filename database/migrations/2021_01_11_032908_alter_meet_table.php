<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMeetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('meet', function (Blueprint $table) {
            $table->string('location', 60)->change();
            $table->float('programfee')->change();
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
            $table->string('location', 60)->nullable()->change();
            $table->float('programfee')->nullable()->change();
        });
    }
}
