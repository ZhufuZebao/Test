<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('type', 1)->after('birth')->default('1')->comment("ユーザー種類:1,職人　2,一般アカウント　3,管理アカウント");
            $table->unsignedInteger('enterprise_id')->nullable()->after('type')->comment("会社ID");
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
            $table->dropColumn('type');
            $table->dropColumn('enterprise_id');
        });
    }
}
