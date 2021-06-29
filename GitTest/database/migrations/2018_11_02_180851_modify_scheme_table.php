<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySchemeTable extends Migration
{
    private $tbl = ['schemes', 'tasks'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->amendTableScheme();
        $this->amendTableTask();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->revertTableScheme();
        $this->revertTableTask();
    }

    private function amendTableScheme()
    {
        $tbl = $this->tbl[0];

        /* Schema::create($tbl, function (Blueprint $table) {
         *     $table->increments('id');
	       $table->unsignedInteger('project_id');
	       $table->string('name', 128);
	       $table->string('note', 255);

	       $table->date('st_date');
	       $table->date('ed_date');

	       $table->unsignedInteger('contractor_id')->nullable();
	       $table->unsignedInteger('user_id')->nullable();
         *     $table->timestamps();
         * });*/

        Schema::table($tbl, function (Blueprint $table) {
            $table->unsignedInteger('parent_id')
                  ->after('project_id')
                  ->nullable();

            $table->integer('weight')
                  ->after('parent_id')
                  ->default(0);
        });
    }

    private function amendTableTask()
    {
        $tbl = $this->tbl[1];

        Schema::table($tbl, function (Blueprint $table) {
            $table->integer('weight')->after('parent_id')->default(0);
        });
    }

    private function revertTableScheme()
    {
        $tbl = $this->tbl[0];

        Schema::table($tbl, function (Blueprint $table) {
            $table->dropColumn('parent_id');
            $table->dropColumn('weight');
        });
    }

    private function revertTableTask()
    {
        $tbl = $this->tbl[1];

        Schema::table($tbl, function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }

}
