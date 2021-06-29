<?php
    /**
     * Created by PhpStorm.
     * User: P0154081
     * Date: 2020/10/30
     * Time: 12:57
     */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAllDaySchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedTinyInteger('all_day')->default('0')->after('notify_min_ago')->comment('終日フラグ:1,終日');
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
        });
    }
}
