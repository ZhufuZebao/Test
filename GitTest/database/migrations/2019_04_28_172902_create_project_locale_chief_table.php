<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectLocaleChiefTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_locale_chief', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->comment('案件ID');
            $table->string('name', 30)->comment('現場担当者氏名');
            $table->string('position', 20)->comment('現場担当者役職');
            $table->string('tel', 15)->comment('現場担当者携帯電話');
            $table->string('mail', 30)->comment('現場担当者メールアドレス');
            $table->unsignedInteger('created_by')->comment('作成者');
            $table->unsignedInteger('updated_by')->comment('更新者');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('project_id')->references('id')->on('projects');
        });
        DB::statement("ALTER TABLE `project_locale_chief` comment '案件現場担当者'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_locale_chief');
    }
}
