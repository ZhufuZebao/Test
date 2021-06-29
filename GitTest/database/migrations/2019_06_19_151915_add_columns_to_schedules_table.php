<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedTinyInteger('all_day')->nullable()->after('notify_min_ago')->comment('終日フラグ:1,終日');
            $table->string('location', 100)->nullable()->after('all_day')->comment('場所');
            $table->unsignedTinyInteger('repeat_type')->nullable()->after('location')->comment('画面の繰り返し種類:0,繰り返さない 1,毎日…');
            $table->unsignedTinyInteger('repeat_interval_type')->nullable()->after('repeat_type')->comment('画面の繰り返し間隔種類:1,週毎 2,月毎');
            $table->unsignedTinyInteger('repeat_interval_value')->nullable()->after('repeat_interval_type')->comment('画面の繰り返し間隔値');
            $table->unsignedTinyInteger('repeat_limit_type')->nullable()->after('repeat_interval_value')->comment('繰り返し期限:1,終了日時 2,繰り返す回数');
            $table->string('repeat_limit_value', 10)->nullable()->after('repeat_limit_type')->comment('繰り返しの終了日時または回数');
            $table->unsignedInteger('created_by')->after('repeat_limit_value')->nullable()->comment('作成者');
            $table->unsignedInteger('updated_by')->after('created_by')->nullable()->comment('更新者');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('all_day');
            $table->dropColumn('location');
            $table->dropColumn('repeat_type');
            $table->dropColumn('repeat_interval_type');
            $table->dropColumn('repeat_interval_value');
            $table->dropColumn('repeat_limit_type');
            $table->dropColumn('repeat_limit_value');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }
}
