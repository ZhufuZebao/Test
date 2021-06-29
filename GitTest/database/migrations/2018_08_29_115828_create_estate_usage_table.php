<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstateUsageTable extends Migration
{
    protected $tbl = 'estate_usages';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',256)->comment("用途");
            $table->timestamps();
        });

        DB::table($this->tbl)->insert([
            'name' => "店舗",
        ]);
        DB::table($this->tbl)->insert([
            'name' => "事務所",
        ]);
        DB::table($this->tbl)->insert([
            'name' => "病院",
        ]);
        DB::table($this->tbl)->insert([
            'name' => "私邸",
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tbl);
    }
}
