<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
