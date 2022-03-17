<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSportstgMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sportstg_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('external_id')->unique();
            $table->string('number');
            $table->string('surname');
            $table->string('firstname');
            $table->unsignedInteger('member_id');
            $table->json('member_data');
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
        Schema::dropIfExists('sportstg_members');
    }
}
