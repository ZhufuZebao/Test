<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMemoScheduleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('memo1', 100)->nullable()->after('ed_span');
            $table->string('memo2', 100)->nullable()->after('memo1');
            $table->string('memo3', 100)->nullable()->after('memo2');
            $table->string('memo4', 100)->nullable()->after('memo3');
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
            $table->dropColumn('memo1');
            $table->dropColumn('memo2');
            $table->dropColumn('memo3');
            $table->dropColumn('memo4');
        });
    }
}
