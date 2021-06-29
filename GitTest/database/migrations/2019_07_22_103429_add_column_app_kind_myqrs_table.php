<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAppKindMyqrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('myqrs', function (Blueprint $table) {
            $table->char('app_kind', 1)->nullable()->after('qrkey');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('myqrs', function (Blueprint $table) {
            $table->dropColumn('app_kind');
        });
    }
}
