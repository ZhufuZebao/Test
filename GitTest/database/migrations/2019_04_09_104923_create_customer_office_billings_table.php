<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerOfficeBillingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_office_billings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_office_id')->references('id')->on('customer_offices')->comment('事業所ID');
            $table->string('name',256)->comment('請求先名');
            $table->string('zip',8)->comment('郵便番号');
            $table->string('pref',4)->comment('都道府県');
            $table->string('town',256)->comment('市区町村');
            $table->string('street',256)->comment('番地');
            $table->string('house',256)->comment('建物名');
            $table->string('tel',64)->comment('電話番号');
            $table->string('fax',64)->comment('FAX');
            $table->string('people_name',256)->comment('担当者名');
            $table->string('position',256)->comment('役職')->nullable();
            $table->string('dept',256)->comment('部署');
            $table->string('email',256)->comment('メールアドレス')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedInteger('user_id')->references('id')->on('users')->comment('作成者');
            $table->foreign('customer_office_id')->references('id')->on('customer_offices');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_office_billings');
    }
}
