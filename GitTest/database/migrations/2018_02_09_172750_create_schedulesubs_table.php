<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchedulesubsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedulesubs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('relation_id')->nullable();     // 関連ID
            $table->unsignedInteger('user_id');
            $table->date('s_date');

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
        Schema::dropIfExists('schedulesubs');
    }
}
