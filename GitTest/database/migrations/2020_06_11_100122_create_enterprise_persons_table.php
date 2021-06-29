<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnterprisePersonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enterprise_persons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('enterprise_intelligences_id')->comment("事業所ID");
            $table->string('name',256)->comment('担当者氏名');
            $table->string('position',256)->comment('役職')->nullable();
            $table->string('dept',256)->comment('部署');
            $table->string('email',256)->comment('メールアドレス');
            $table->string('tel',64)->comment('電話番号');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('enterprise_intelligences_id')->references('id')->on('enterprise_intelligences');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enterprise_persons');
    }
}
