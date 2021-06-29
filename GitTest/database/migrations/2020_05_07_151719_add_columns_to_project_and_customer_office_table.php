<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToProjectAndCustomerOfficeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('tel_in',15)->nullable()->after('tel')
                ->comment('内線');
        });
        Schema::table('customer_offices', function (Blueprint $table) {
            $table->string('tel_in', 15)->nullable()->after('tel')
                ->comment('内線');
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
            $table->dropColumn('tel_in');
        });
        Schema::table('customer_offices', function (Blueprint $table) {
            $table->dropColumn('tel_in');
        });
    }
}
