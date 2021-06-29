<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->date('s_date');
            $table->char('st_time', 5)->nullable();
            $table->char('ed_time', 5)->nullable();
            $table->unsignedInteger('type');
            $table->text('subject')->nullable();
            $table->text('comment')->nullable();

            $table->char('repeat_kbn', 1)->nullable();  // 0:1日, 1:毎日, 2:毎週, 3:毎月, 4:曜日指定
            $table->char('week1', 1)->nullable();    // 1:日
            $table->char('week2', 1)->nullable();    // 2:月
            $table->char('week3', 1)->nullable();    // 3:火
            $table->char('week4', 1)->nullable();    // 4:水
            $table->char('week5', 1)->nullable();    // 5:木
            $table->char('week6', 1)->nullable();    // 6:金
            $table->char('week7', 1)->nullable();    // 7:土

            $table->date('st_span')->nullable();
            $table->date('ed_span')->nullable();

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
        Schema::dropIfExists('schedules');
    }
}
