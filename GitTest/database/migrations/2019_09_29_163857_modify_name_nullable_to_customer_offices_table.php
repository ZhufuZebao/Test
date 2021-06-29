<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyNameNullableToCustomerOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_offices', function (Blueprint $table) {
            $table->string('name', 256)->comment('事業所名')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_offices', function (Blueprint $table) {
            $table->string('name', 256)->comment('事業所名')->change();
        });
    }
}
