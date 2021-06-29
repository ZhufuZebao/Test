<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
	        $table->unsignedInteger('parent_id')->nullable();

	        $table->date('st_date');
	        $table->date('ed_date');
	        $table->string('name',128);
	        $table->string('note',256)->nullable();
	        $table->unsignedInteger('user_id')->nullable();

            $table->timestamps();
        });

	    DB::table('tasks')->insert([
            'st_date'   => '2018-06-06',
            'ed_date'   => '2018-06-30',
	        'user_id'   => null,
	        'note'      => 'hello',
	        'name'      => 'task 000',
	    ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}

