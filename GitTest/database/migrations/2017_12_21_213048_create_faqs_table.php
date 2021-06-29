<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFaqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->Integer('post_id');
            $table->Integer('sub_id');
            $table->unsignedInteger('contractor_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();

//            $table->char('question', 1);
//            $table->char('answer', 1);

            $table->string('title', 190);
            $table->text('comment');
            $table->dateTime('post_date');

            $table->dateTime('fix_date')->nullable();
            $table->smallInteger('waitday')->default(0);

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
        Schema::dropIfExists('faqs');
    }
}
