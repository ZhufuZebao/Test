<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DelNotNullToProjectTradesChiefTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_trades_chief', function (Blueprint $table) {
            $table->string('trades_type', 1)->nullable()->change()->comment('工種別:1,電気 2,ガス 3,水道 4,空調 5,その他');
            $table->string('tel', 15)->nullable()->change()->comment('連絡先電話番号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_trades_chief', function (Blueprint $table) {
            $table->string('trades_type', 1)->nullable(false)->change()->comment('工種別:1,電気 2,ガス 3,水道 4,空調 5,その他');
            $table->string('tel', 15)->nullable(false)->change()->comment('連絡先電話番号');
        });
    }
}
