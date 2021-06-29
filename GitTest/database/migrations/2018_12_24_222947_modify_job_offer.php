<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyJobOffer extends Migration
{
    private $tbl = ['job_offers'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->modifyJobOffers();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->restoreJobOffer();
    }

    private function modifyJobOffers()
    {
        $tbl = $this->tbl[0];

        Schema::table($tbl, function (Blueprint $table) {
            $table->smallInteger('accepted')->after('hired')->nullable();
        });
    }

    private function restoreJobOffer()
    {
        $tbl = $this->tbl[0];

        Schema::table($tbl, function (Blueprint $table) {
            $table->dropColumn('accepted');
        });
    }
}
