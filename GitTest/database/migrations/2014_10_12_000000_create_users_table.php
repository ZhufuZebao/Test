<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            $table->date('birth')->nullable();
            $table->char('sex',1)->nullable();

            $table->char('zip', 8)->nullable();
            $table->unsignedInteger('pref')->nullable();
            $table->string('addr',190)->nullable();

            $table->string('telno1',16)->nullable();
            $table->string('telno2',16)->nullable();
            $table->string('comp',64)->nullable();
            $table->string('class',32)->nullable();
            $table->string('file',64)->nullable();
            $table->rememberToken();

            //$table->timestamps();

            /////////////////////////////////////////////////////////////////////////

            //$table->increments('id');

            //$table->string('name');
            $table->string('first_name', 60);       // 名
            $table->string('last_name', 60);        // 姓

            //$table->string('email')->unique();  // email
            //$table->string('password');         // パスワード

            $table->integer('category1')->nullable();         // 職種・対応可能な分野
            $table->integer('category2')->nullable();         // 職種・対応可能な分野
            $table->integer('category3')->nullable();         // 職種・対応可能な分野

            $table->integer('specialty')->nullable();         // 得意な分野

            //$table->char('zip', 8)->nullable();
            //$table->unsignedInteger('pref')->nullable();      // 都道府県
            $table->unsignedInteger('addr_code')->nullable();   // 市区町村

            $table->integer('area1')->nullable();         // 対応可能エリア
            $table->integer('area2')->nullable();         // 対応可能エリア
            $table->integer('area3')->nullable();         // 対応可能エリア
            $table->integer('area4')->nullable();         // 対応可能エリア
            $table->integer('area5')->nullable();         // 対応可能エリア

            //$table->date('birth')->nullable();
            //$table->char('sex',1);

            $table->string('license')->nullable();          // 免許資格
            $table->string('career')->nullable();           // 職歴
            $table->string('belong')->nullable();           // 所属
            $table->string('skill')->nullable();            // スキル

            $table->string('desired_condition')->nullable();    // 希望条件

            $table->smallInteger('flag1')->nullable();         // 未経験フラグ
            $table->smallInteger('flag2')->nullable();         // 師匠募集フラグ
            $table->smallInteger('flag3')->nullable();         // 弟子希望フラグ

            //$table->string('telno',16)->nullable();

            $table->string('dream')->nullable();         // 将来の夢
            $table->string('motto')->nullable();         // 座右の銘
            $table->string('things_to_realize')->nullable();         // 3～5年で実現したいこと

            //$table->string('file',64)->nullable();

            //$table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
