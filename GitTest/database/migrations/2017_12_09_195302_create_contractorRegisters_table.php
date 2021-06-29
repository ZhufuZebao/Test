<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContractorRegistersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contractorRegisters', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',128);//会社名
            $table->char('zip', 8)->nullable();//郵便番号
            $table->unsignedInteger('pref');//都道府県
            $table->string('village');//市区町村
            $table->string('addr',190);//番地
            $table->string('building');//建物名
            $table->string('phone', 100);//電話番号
            $table->string('manager_name', 100);//管理者名
            $table->string('manager_mail', 100);//管理者メールアドレス
            $table->string('manager_password', 100);//管理者パスワード
            $table->text('contents')->nullable();
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
        Schema::dropIfExists('contractorRegisters');
    }
}
