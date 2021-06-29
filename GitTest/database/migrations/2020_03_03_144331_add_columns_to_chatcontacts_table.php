<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToChatcontactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatcontacts', function (Blueprint $table) {
            $table->string('append_status',1)->after('contact_agree')
                ->comment('招待した場合:0,名前検索から招待した場合で 1,メールアドレスから追加で招待した場合で');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatcontacts', function (Blueprint $table) {
            $table->dropColumn('append_status');
        });
    }
}
