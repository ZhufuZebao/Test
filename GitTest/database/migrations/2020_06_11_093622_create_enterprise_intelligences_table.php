<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnterpriseIntelligencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enterprise_intelligences', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('enterprise_id')->comment("会社ID");
            $table->string('name',256)->comment('名前');
            $table->string('phonetic',256)->comment('名前カタカナ');
            $table->string('zip',8)->comment('郵便番号');
            $table->string('pref',4)->comment('都道府県');
            $table->string('town',256)->comment('市区町村');
            $table->string('street',256)->comment('番地');
            $table->string('house',256)->nullable()->comment('建物名');
            $table->string('tel',64)->comment('電話番号');
            $table->string('fax',64)->nullable()->comment('FAX');
            $table->string('type',1)->default('1')
                ->comment('種別: 1.管理会社 2.不動産会社 3.消防署 4.警察署 5.病院');
            $table->text('remarks')->nullable()->comment('備考');
            $table->softDeletes();
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
        Schema::dropIfExists('enterprise_intelligences');
    }
}
