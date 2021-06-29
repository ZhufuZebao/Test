<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAppKindFirebaseidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('firebaseids', function (Blueprint $table) {
            $table->char('app_kind', 1)->nullable()->after('firebase_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('firebaseids', function (Blueprint $table) {
            $table->dropColumn('app_kind');
        });
    }
}
