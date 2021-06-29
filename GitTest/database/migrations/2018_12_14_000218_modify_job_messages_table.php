<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ModifyJobMessagesTable extends \Illuminate\Database\Migrations\Migration
{
    private $tbl = ['job_offer_messages', 'job_offers'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->modifyMessagesTable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->revertMessagesTable();
    }

    private function modifyMessagesTable()
    {
        $tbl = $this->tbl[0];

        Schema::table($tbl, function (Blueprint $table) {
            $table->unsignedInteger('receiver_id')->after('sender_id');
	    $table->timestamp('read_at')->nullable()->after('content');
        });
    }

    private function revertMessagesTable()
    {
        $tbl = $this->tbl[0];

        Schema::table($tbl, function (Blueprint $table) {
            $table->dropColumn('read_at');
            $table->dropColumn('receiver_id');
        });
    }

}
