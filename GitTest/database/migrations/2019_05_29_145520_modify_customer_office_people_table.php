<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCustomerOfficePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customer_office_people', function (Blueprint $table) {
            $table->string('position', 256)->nullable()->change()->comment('役職');
            $table->string('dept', 256)->nullable()->change()->comment('部署');
            $table->string('role', 256)->nullable()->change()->comment('担当区分');
            $table->string('email', 256)->nullable()->change()->comment('メールアドレス');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_office_people', function (Blueprint $table) {
            $table->string('position', 256)->nullable(false)->change()->comment('役職');
            $table->string('dept', 256)->nullable(false)->change()->comment('部署');
            $table->string('role', 256)->nullable(false)->change()->comment('担当区分');
            $table->string('email', 256)->nullable(false)->change()->comment('メールアドレス');
        });
    }
}
