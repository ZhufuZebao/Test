<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerOfficePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_office_people', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_office_id')->references('id')->on('customer_offices')->comment('事業所ID');
            $table->string('name',256)->comment('担当者氏名');
            $table->string('position',256)->comment('役職')->nullable();
            $table->string('dept',256)->comment('部署');
            $table->string('role',256)->comment('担当区分');
            $table->string('email',256)->comment('メールアドレス');
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
        Schema::dropIfExists('customer_office_people');
    }
}
