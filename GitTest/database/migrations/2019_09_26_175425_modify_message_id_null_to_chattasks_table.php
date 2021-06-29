<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyMessageIdNullToChattasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chattasks', function (Blueprint $table) {
            $table->unsignedInteger('message_id')->nullable()->change()->after('notify');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chattasks', function (Blueprint $table) {
            $table->unsignedInteger('message_id')->default(0)->change()->after('complete_date');
        });
    }
}
