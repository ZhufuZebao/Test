<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToReportFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_files', function (Blueprint $table) {
            //
            $table->date('report_date')->nullable()->after('file_path')->comment("作業日");
            $table->string('work_place',191)->nullable()->after('report_date')->comment('作業箇所');
            $table->string('weather',191)->nullable()->after('work_place')->comment('天気');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_files', function (Blueprint $table) {
            //
            $table->dropColumn('report_date');
            $table->dropColumn('work_place');
            $table->dropColumn('weather');
        });
    }
}
