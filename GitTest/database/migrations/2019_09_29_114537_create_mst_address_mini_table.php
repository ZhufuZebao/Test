<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMstAddressMiniTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_address_mini', function (Blueprint $table) {
            $table->char('officialCode', 11)->comment('全国地方公共団体コード:(JIS X0401、X0402)');
            $table->string('pref', 20)->comment('都道府県名');
            $table->string('city', 40)->comment('市区町村名');
            $table->primary('officialCode');
        });
        DB::statement('ALTER TABLE mst_address_mini MODIFY COLUMN officialCode INT(5) UNSIGNED ZEROFILL NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_address_mini');
    }
}
