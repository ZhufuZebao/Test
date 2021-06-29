<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChangelogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('change_logs', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id')->comment("作成者");
            $table->char('tbl_name');
            $table->unsignedInteger('tbl_id');
            $table->binary('before');
            $table->binary('after');
            $table->char('ip')->nullable();
            $table->char('url')->nullable();
            
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
        Schema::dropIfExists('change_logs');
    }
}
