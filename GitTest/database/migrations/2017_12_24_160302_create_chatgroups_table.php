<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatgroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatgroups', function(Blueprint $table)
        {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('group_id');
            $table->smallInteger('admin');  // 1:管理者権限, 0:メンバー

            //$table->primary('id');


            //            $table->integer('user_id')->unsigned()->index();
            //            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            //            $table->integer('group_id')->unsigned()->index();
            //            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');

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
        Schema::dropIfExists('chatgroups');
    }
}
