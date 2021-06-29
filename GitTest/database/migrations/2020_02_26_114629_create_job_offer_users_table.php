<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobOfferUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_offer_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('job_offer_id');
            $table->unsignedInteger('user_id');
            $table->string('status',1)->default('0')->comment("0:交渉中,1:承諾済,2:拒否");
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
        Schema::dropIfExists('job_offer_users');
    }
}
