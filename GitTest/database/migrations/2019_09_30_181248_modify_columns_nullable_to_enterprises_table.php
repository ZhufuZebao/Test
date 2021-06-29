<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsNullableToEnterprisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enterprises', function (Blueprint $table) {
            $table->string('zip', 7)->comment('郵便番号')->nullable()->change();
            $table->string('pref', 20)->comment('都道府県')->nullable()->change();
            $table->string('town', 30)->comment('市区町村')->nullable()->change();
            $table->string('street', 20)->comment('番地')->nullable()->change();
            $table->string('tel', 15)->comment('電話番号')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enterprises', function (Blueprint $table) {
            $table->string('zip', 7)->comment('郵便番号')->change();
            $table->string('pref', 20)->comment('都道府県')->change();
            $table->string('town', 30)->comment('市区町村')->change();
            $table->string('street', 20)->comment('番地')->change();
            $table->string('tel', 15)->comment('電話番号')->change();
        });
    }
}
