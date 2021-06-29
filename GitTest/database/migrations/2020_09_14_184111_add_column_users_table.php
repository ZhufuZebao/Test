<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('ip', 15)->after('last_name')->nullable()->comment("ip");
            $table->date('last_date')->after('ip')->nullable()->comment("最後ログイン時間");
            $table->string('block',1)->after('last_date')->default('0')->comment("ブロック:0,ブロック解除  1,ブロック");
            $table->text('block_message')->after('block')->nullable()->comment("ブロック理由");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('last_date');
            $table->dropColumn('block');
            $table->dropColumn('block_message');
        });
    }
}
