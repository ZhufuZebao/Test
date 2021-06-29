<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsersCloumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 60)->nullable()->change()->comment('名');
            $table->string('last_name', 60)->nullable()->change()->comment('姓');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 60)->nullable(false)->change()->comment('名');
            $table->string('last_name', 60)->nullable(false)->change()->comment('姓');
        });
    }
}
