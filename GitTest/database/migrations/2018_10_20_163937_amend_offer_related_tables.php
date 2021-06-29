<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AmendOfferRelatedTables extends Migration
{
    private $tbl = [
        'job_vacancies',
        'job_offers',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();

        $this->amendJobVacancy();
        $this->amendJobOffer();

        $this->insertMasterData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        echo "skip rollback of this migration";
        return false;

        /*
        $this->rollbackJobVacancy();
        $this->rollbackJobOffer();
         */
    }

    private function amendJobVacancy()
    {
        $tbl = $this->tbl[0];

        Schema::table($tbl, function (Blueprint $table) {
            $table->dropColumn('status');
            $table->unsignedInteger('status_id')->before('created_at');

        });
    }

    private function amendJobOffer()
    {
        $tbl = $this->tbl[1];

        Schema::table($tbl, function (Blueprint $table) {

            $table->unsignedInteger('vacancy_id')->after('id');

        });
    }

    private function insertMasterData()
    {
        $rows = [
            ['id' => 1 ,'name' => "停止"],
            ['id' => 2 ,'name' => "一時保存"],
            ['id' => 3 ,'name' => "公開"],
        ];
        \App\JobVacancyStatus::insert($rows);
    }

    private function rollbackJobVacancy()
    {
        $tbl = $this->tbl[0];

        Schema::table($tbl, function (Blueprint $table) {


            $table->dropColumn('status_id');
            $table->unsignedInteger('status')->before('created_at');
        });
    }

    private function rollbackJobOffer()
    {
        $tbl = $this->tbl[1];

        Schema::table($tbl, function (Blueprint $table) {


            $table->dropColumn('status_id');
            $table->unsignedInteger('status')->before('created_at');
        });
    }
}
