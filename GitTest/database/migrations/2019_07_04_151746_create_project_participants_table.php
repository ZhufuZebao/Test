<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->comment('案件ID');
            $table->unsignedInteger('user_id')->comment('関係者ID');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `project_participants` comment '案件関係者'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_participants');
    }
}
