<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnsToDashboardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->char('sort_time')->nullable()
                ->comment('新着情報ソート時間')->after('to_user_id');
            $table->char('read',1)->nullable()
                ->comment('0：unread，1：read')->nullable()->after('sort_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dashboards', function (Blueprint $table) {
            $table->dropColumn('sort_time');
            $table->dropColumn('read');
        });
    }
}
