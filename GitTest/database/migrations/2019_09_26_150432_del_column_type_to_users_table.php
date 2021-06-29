<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DelColumnTypeToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('category');
            $table->dropColumn('category1');
            $table->dropColumn('category2');
            $table->dropColumn('category3');
            $table->dropColumn('area1');
            $table->dropColumn('area2');
            $table->dropColumn('area3');
            $table->dropColumn('area4');
            $table->dropColumn('area5');
            $table->dropColumn('career');
            $table->dropColumn('belong');
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
            $table->string('type', 1)->default(1)->after('birth')->comment('ユーザー種類: 1,職人 2,一般アカウント 3,管理アカウント');
            $table->string('category', 191)->nullable()->after('last_name');
            $table->integer('category1')->nullable()->after('category');
            $table->integer('category2')->nullable()->after('category1');
            $table->integer('category3')->nullable()->after('category2');
            $table->integer('area1')->nullable()->after('addr_code');
            $table->integer('area2')->nullable()->after('area1');
            $table->integer('area3')->nullable()->after('area2');
            $table->integer('area4')->nullable()->after('area3');
            $table->integer('area5')->nullable()->after('area4');
            $table->string('career', 191)->nullable()->after('license');
            $table->string('belong', 191)->nullable()->after('career');
        });
    }
}
