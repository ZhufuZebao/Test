<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_offices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id')->references('id')->on('customers')->comment('施主ID');
            $table->string('name',256)->comment('事業所名');
            $table->string('zip',8)->comment('郵便番号');
            $table->string('pref',4)->comment('都道府県');
            $table->string('town',256)->comment('市区町村');
            $table->string('street',256)->comment('番地');
            $table->string('house',256)->comment('建物名');
            $table->string('tel',64)->comment('電話番号');
            $table->string('fax',64)->comment('FAX');
            $table->softDeletes();
            $table->timestamps();
            $table->unsignedInteger('user_id')->references('id')->on('users')->comment('作成者');
            $table->foreign('customer_id')->references('id')->on('customers');
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
        Schema::dropIfExists('customer_offices');
    }
}
