<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyJobOfferTable extends Migration
{
    private $tbl = ['job_offers'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->amendTableJobOffer();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->revertTableJobOffer();
    }

    private function amendTableJobOffer()
    {
        $tbl = $this->tbl[0];

        Schema::table($tbl, function (Blueprint $table) {
            $table->smallInteger('hired')
                  ->after('user_id')
                  ->nullable();
        });
    }

    private function revertTableJobOffer()
    {
        $tbl = $this->tbl[0];

        Schema::table($tbl, function (Blueprint $table) {
            $table->dropColumn('hired');
        });
    }

}
