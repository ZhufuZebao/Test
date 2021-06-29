<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCustomerOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_offices', function (Blueprint $table) {
            $table->string('house', 256)->comment('建物名')->nullable()->change();
            $table->string('fax', 64)->comment('FAX')->nullable()->change();
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
            $table->string('house', 256)->comment('建物名')->nullable(false)->change();
            $table->string('fax', 64)->comment('FAX')->nullable(false)->change();
        });
    }
}
