<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DelNotNullToProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            // nullableに変更
            $table->string('zip', 7)->nullable()->change()->comment('郵便番号')->after('construction_name');
            $table->string('pref', 20)->nullable()->change()->comment('都道府県')->after('zip');
            $table->string('town', 50)->nullable()->change()->comment('区市町村')->after('pref');
            $table->string('street', 50)->nullable()->change()->comment('番地')->after('town');
            $table->string('address', 170)->nullable()->change()->comment('案件場所')->after('house');
            $table->string('tel', 15)->nullable()->change()->comment('電話番号')->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('zip', 7)->nullable(false)->change()->comment('郵便番号')->after('construction_name');
            $table->string('pref', 20)->nullable(false)->change()->comment('都道府県')->after('zip');
            $table->string('town', 50)->nullable(false)->change()->comment('区市町村')->after('pref');
            $table->string('street', 50)->nullable(false)->change()->comment('番地')->after('town');
            $table->string('address', 170)->nullable(false)->change()->comment('案件場所')->after('house');
            $table->string('tel', 15)->nullable(false)->change()->comment('電話番号')->after('latitude');
        });
    }
}
