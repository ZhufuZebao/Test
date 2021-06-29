<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectNoToProjects extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            //#2790
            $table->string('project_no', 50)->after('place_name')->nullable()->comment('案件No');
            $table->dropColumn('construction_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            //#2790
            $table->dropColumn('project_no');
            $table->string('construction_name', 50)->comment('工事件名')->after('place_name');
        });
    }
}
