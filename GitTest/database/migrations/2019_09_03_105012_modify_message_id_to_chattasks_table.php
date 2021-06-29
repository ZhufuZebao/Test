<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyMessageIdToChattasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chattasks', function (Blueprint $table) {
            // ディフォルト値を設定
            $table->unsignedInteger('message_id')->default(0)->change()->after('complete_date');
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
            $table->unsignedInteger('message_id')->change()->after('complete_date');
        });
    }
}
