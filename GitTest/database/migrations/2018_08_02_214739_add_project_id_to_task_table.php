<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectIdToTaskTable extends Migration
{
    private $tblName = 'tasks';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tblName, function (Blueprint $table) {
            $table->string('project_id')->after('id')->required()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tblName, function (Blueprint $table) {
            $table->dropColumn('project_id');
        });
    }
}
