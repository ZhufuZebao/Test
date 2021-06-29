<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtToChatgroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatgroups', function (Blueprint $table) {
            // 2020-10-26 #2298  【データ削除の洗い出し】削除機能を論理削除に切り替えるための洗い出し
            $table->softDeletes()->comment('削除时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatgroups', function (Blueprint $table) {
            //
            $table->dropColumn('deleted_at');
        });
    }
}
