<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotifyToChattasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chattasks', function (Blueprint $table) {
            $table->integer('notify')->nullable()->after('complete_date')->comment('通知設定:（NULL=通知なし、0=期限の時刻, 5=5分前, 10=10分前, 30=30分前, 60=１時間前, 1440=前日）)');
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
            $table->dropColumn('notify');
        });
    }
}
