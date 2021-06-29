<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCustomerOfficeBillingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_office_billings', function (Blueprint $table) {
            $table->string('house', 256)->nullable()->change()->comment('建物名');
            $table->string('fax', 64)->nullable()->change()->comment('FAX');
            $table->string('people_name', 256)->nullable()->change()->comment('担当者名');
            $table->string('dept', 256)->nullable()->change()->comment('部署');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_office_billings', function (Blueprint $table) {
            $table->string('house', 256)->nullable(false)->change()->comment('建物名');
            $table->string('fax', 64)->nullable(false)->change()->comment('FAX');
            $table->string('people_name', 256)->nullable(false)->change()->comment('担当者名');
            $table->string('dept', 256)->nullable(false)->change()->comment('部署');
        });
    }
}
