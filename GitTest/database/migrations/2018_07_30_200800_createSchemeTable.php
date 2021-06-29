<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchemeTable extends Migration
{
    private $tblName = 'schemes';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tblName, function (Blueprint $table) {
            $table->increments('id');
	        $table->unsignedInteger('project_id');
	        $table->string('name', 128);
	        $table->string('note', 255);

	        $table->date('st_date');
	        $table->date('ed_date');

	        $table->unsignedInteger('contractor_id')->nullable();
	        $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tblName);
    }
}
