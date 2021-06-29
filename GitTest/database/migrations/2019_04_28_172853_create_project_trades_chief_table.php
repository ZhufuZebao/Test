<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectTradesChiefTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_trades_chief', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->comment('案件ID');
            $table->string('trades_type', 1)->comment('工種別:1,電気 2,ガス 3,水道 4,空調 5,その他');
            $table->string('trades_type_detail', 20)->nullable()->comment('工種別詳細入力');
            $table->string('company', 30)->nullable()->comment('工種別会社名');
            $table->string('name', 30)->comment('工種別責任者');
            $table->string('tel', 15)->comment('連絡先電話番号');
            $table->unsignedInteger('created_by')->comment('作成者');
            $table->unsignedInteger('updated_by')->comment('更新者');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('project_id')->references('id')->on('projects');
        });
        DB::statement("ALTER TABLE `project_trades_chief` comment '案件工種別責任者'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_trades_chief');
    }
}
