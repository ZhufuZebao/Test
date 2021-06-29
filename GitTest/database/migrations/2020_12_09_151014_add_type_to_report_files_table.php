<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToReportFilesTable extends Migration
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
            $table->smallInteger('type')->default(0)->after('comment')->nullable()->comment('レポートタイプ');
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
            $table->dropColumn('type');
        });
    }
}
