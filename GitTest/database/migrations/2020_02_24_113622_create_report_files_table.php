<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_files', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('report_id')->comment("レポートID");
            $table->string('file_path',256)->comment("アップロードの画像パス");
            $table->text('comment')->comment("コメント")->nullable();
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
        Schema::dropIfExists('report_files');
    }
}
