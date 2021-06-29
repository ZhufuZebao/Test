<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrowsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('browses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('enterprise_id')->comment("会社ID");
            $table->integer('chat')->nullable()->default(0)->comment("チャット_使用頻度");
            $table->integer('schedule')->nullable()->default(0)->comment("スケジュール_使用頻度");
            $table->integer('document')->nullable()->default(0)->comment("ドキュメント_使用頻度");
            $table->integer('report')->nullable()->default(0)->comment("簡易レポート_使用頻度");
            $table->integer('invite')->nullable()->default(0)->comment("協力会社_使用頻度");
            $table->integer('friend')->nullable()->default(0)->comment("職人_使用頻度");
            $table->integer('project')->nullable()->default(0)->comment("案件_使用頻度");
            $table->integer('customer')->nullable()->default(0)->comment("施主_使用頻度");
            $table->integer('profile')->nullable()->default(0)->comment("プロフィール_使用頻度");
            $table->integer('account')->nullable()->default(0)->comment("アカウント_使用頻度");
            $table->timestamps();
            $table->foreign('enterprise_id')->references('id')->on('enterprises');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('browses');
    }
}
