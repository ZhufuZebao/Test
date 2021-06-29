<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DelNotNullToProjectLocaleChiefTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_locale_chief', function (Blueprint $table) {
            $table->string('position', 20)->nullable()->change()->comment('現場担当者役職');
            $table->string('tel', 15)->nullable()->change()->comment('現場担当者携帯電話');
            $table->string('mail', 30)->nullable()->change()->comment('現場担当者メールアドレス');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_locale_chief', function (Blueprint $table) {
            $table->string('position', 20)->nullable(false)->change()->comment('現場担当者役職');
            $table->string('tel', 15)->nullable(false)->change()->comment('現場担当者携帯電話');
            $table->string('mail', 30)->nullable(false)->change()->comment('現場担当者メールアドレス');
        });
    }
}
