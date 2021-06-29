<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('works', function (Blueprint $table) {
            $table->increments('id');
//            $table->unsignedInteger('project_id');
            $table->unsignedInteger('contractor_id');
//            $table->unsignedInteger('type_id');

            $table->text('name');
            $table->text('contents');
            $table->text('experiense')->nullable();
            $table->date('st_date');
            $table->date('ed_date');
            $table->string('st_time',8);
            $table->string('ed_time',8);
            $table->string('place',128);
//            $table->string('manager',32)->nullable();
            $table->text('condition');
            $table->unsignedInteger('contract_id');
            $table->text('salary')->nullable();
            $table->integer('pay_max')->nullable();
            $table->integer('pay_min')->nullable();
//            $table->string('author',32)->nullable();
            $table->date('create_date')->nullable();
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
        Schema::dropIfExists('works');
    }
}
