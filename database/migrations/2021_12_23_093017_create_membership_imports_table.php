<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembershipImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('membership_imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('requested_at')->default(DB::raw('NOW()'));
            $table->timestamp('started_at')->default(DB::raw('NOW()'));
            $table->timestamp('finished_at')->nullable();
            $table->string('source');
            $table->string('filename')->nullable();
            $table->string('checksum')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('members')->nullable();
            $table->unsignedInteger('members_new')->nullable();
            $table->unsignedInteger('members_updated')->nullable();
            $table->unsignedInteger('members_renewed')->nullable();
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
        Schema::dropIfExists('membership_imports');
    }
}
