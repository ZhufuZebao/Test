<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMstCategory2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_category2', function (Blueprint $table) {
            $table->integer('category1_id');
            $table->integer('id');
            $table->string('name', 100)->comment('職種・対応可能な分野の中区分名称');
            $table->primary(['category1_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('mst_category2');
    }
}
