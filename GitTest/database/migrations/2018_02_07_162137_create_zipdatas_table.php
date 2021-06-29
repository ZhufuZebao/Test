<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateZipdatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zipdatas', function (Blueprint $table) {
            $table->Integer('id');
            $table->char('zipcode', 7);
            $table->string('state', 8);
            $table->string('city', 22);
            $table->string('town', 74);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zipdatas');
    }
}
