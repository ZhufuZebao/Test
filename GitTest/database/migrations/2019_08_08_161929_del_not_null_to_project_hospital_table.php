<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DelNotNullToProjectHospitalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_hospital', function (Blueprint $table) {
            $table->string('tel', 15)->nullable()->change()->comment('最寄病院電話番号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_hospital', function (Blueprint $table) {
            $table->string('tel', 15)->nullable(false)->change()->comment('最寄病院電話番号');
        });
    }
}
