<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatlistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatlists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('owner_id')->comment('チャット所有者ID');
            $table->unsignedInteger('user_id')->nullable()->comment('相手のユーザーID');
            $table->unsignedInteger('group_id')->nullable()->comment('相手のグループID');
            $table->unsignedTinyInteger('top')->default(0)->comment('一覧の上に表示フラグ:1,上に表示');
            $table->timestamps();
        });
        DB::statement("ALTER TABLE `chatlists` comment 'チャット一覧'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chatlists');
    }
}
