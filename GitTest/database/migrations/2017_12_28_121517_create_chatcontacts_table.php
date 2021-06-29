<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatcontactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatcontacts', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('from_user_id');
            $table->unsignedInteger('to_user_id')->nullable();

            $table->string('email')->nullable();

            $table->datetime('contact_date');
            $table->text('contact_message')->nullable();
            $table->text('agree_message')->nullable();
            $table->char('contact_agree', 1);


//            $table->foreign('to_user_id')
//            ->references('id')
//            ->on('users');

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
        Schema::dropIfExists('chatcontacts');
    }
}
