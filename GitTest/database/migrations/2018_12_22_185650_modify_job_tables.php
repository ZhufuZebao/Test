<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyJobTables extends Migration
{
    private $tbl = ['job_offers'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->modifyJobVacancy();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->restoreJobVacancy();
    }

    private function modifyJobVacancy()
    {
        $tbl = $this->tbl[0];

        Schema::table($tbl, function (Blueprint $table) {
            $table->unsignedInteger('created_by');
            $table->renameColumn('user_id', 'worker_id');
        });
    }

    private function restoreJobVacancy()
    {
        $tbl = $this->tbl[0];

        Schema::table($tbl, function (Blueprint $table) {
            $table->renameColumn('worker_id', 'user_id');
            $table->dropColumn('created_by');
        });
    }
}
