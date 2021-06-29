<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectHospitalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_hospital', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->comment('案件ID');
            $table->string('name', 50)->comment('最寄病院名');
            $table->string('tel', 15)->comment('最寄病院電話番号');
            $table->unsignedInteger('created_by')->comment('作成者');
            $table->unsignedInteger('updated_by')->comment('更新者');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('project_id')->references('id')->on('projects');
        });
        DB::statement("ALTER TABLE `project_hospital` comment '案件最寄病院'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_hospital');
    }
}
