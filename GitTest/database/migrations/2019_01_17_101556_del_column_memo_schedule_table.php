<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DelColumnMemoScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn('memo1');
            $table->dropColumn('memo2');
            $table->dropColumn('memo3');
            $table->dropColumn('memo4');


            $table->dropColumn('user_id');
            $table->dropColumn('s_date');
            $table->dropColumn('st_time');
            $table->dropColumn('ed_time');
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
            $table->string('memo1', 100)->nullable()->after('ed_span');
            $table->string('memo2', 100)->nullable()->after('memo1');
            $table->string('memo3', 100)->nullable()->after('memo2');
            $table->string('memo4', 100)->nullable()->after('memo3');

            $table->unsignedInteger('user_id')->after('id');
            $table->date('s_date')->after('user_id');
            $table->char('st_time', 5)->nullable()->after('s_date');
            $table->char('ed_time', 5)->nullable()->after('st_time');
        });
    }
}
