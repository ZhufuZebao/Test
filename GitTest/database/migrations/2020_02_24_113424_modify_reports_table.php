<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('reports');
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->comment("案件ID");
            $table->date('report_date')->comment("作業日時");
            $table->unsignedInteger('user_id')->comment("報告者");
            $table->string('location')->comment("現場名")->nullable();
            $table->string('type',256)->comment("レポートタイプ: 1,フォーマル 2,カジュアル")->default('1');
            $table->string('file_path',256)->comment("作成したレポートパス")->nullable();
            $table->date('file_date')->comment("レポート作成日時")->nullable();

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
        Schema::dropIfExists('reports');
        Schema::create('reports', function (Blueprint $table) {
            $table->increments('id');
            $table->date('log_date')->comment("作成日時");
            $table->unsignedInteger('user_id')->comment("作成者");
            $table->string('work_type',256)->comment("工種（主に職人の職長が利用）")->nullable()->defaultNull();
            $table->string('title',256)->comment("作業内容")->nullable();
            $table->string('worker',256)->comment("作業担当者（複数入力可）")->nullable();
            $table->string('location',256)->comment("現場名")->nullable();
            $table->string('note',256)->comment("コメント")->nullable();
            $table->string('goal',256)->comment("明日の目標")->nullable();
            $table->string('tips',256)->comment("明日気を付けること")->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });
    }

}
