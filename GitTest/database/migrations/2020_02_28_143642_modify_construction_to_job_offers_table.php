<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyConstructionToJobOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('job_offers');
        Schema::create('job_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title',32)->comment("オファータイトル");
            $table->string('status',1)->default('0')->comment("0:募集中,1:募集終了");
            $table->string('skill',16)->comment("職種");
            $table->string('zip', 7)->comment('郵便番号')->nullable();
            $table->string('pref', 20)->comment('都道府県')->nullable();
            $table->string('town', 30)->comment('市区町村')->nullable();
            $table->string('street', 20)->comment('番地')->nullable();
            $table->string('house', 70)->nullable()->comment('建物名')->nullable();
            $table->text('summary')->comment('作業概要')->nullable();
            $table->date('operation_st_date')->comment("作業期間_開始日");
            $table->date('operation_ed_date')->comment("作業期間_終了日");
            $table->string('recruitment_count', 3)->comment('募集人数');
            $table->date('recruitment_st_date')->comment("募集開始日");
            $table->date('recruitment_ed_date')->comment("募集終了日");
            $table->unsignedInteger('from_user_id');
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
        Schema::dropIfExists('job_offers');
        Schema::create('job_offers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vacancy_id');
            $table->unsignedInteger('worker_id');
            $table->smallInteger('hired')->nullable();
            $table->smallInteger('accepted')->nullable();
            $table->unsignedInteger('created_by');
            $table->timestamps();
        });
    }
}
