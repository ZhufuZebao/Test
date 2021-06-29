<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyDashboardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('dashboards');
        Schema::create('dashboards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('content')->nullable();
            $table->unsignedInteger('related_id');
            $table->char('type',1)
                ->comment('種類:0,チャット,1,案件チャット,2,タスク,3,スケジュール,4,案件,5,ドキュメント,6,協力会社,7,職人,8,施主');
            $table->char('pinned_mark',1)->comment('ピン止めマークを表示:1,固定表示,0,非固定表示')->nullable();
            $table->unsignedInteger('from_user_id');
            $table->unsignedInteger('to_user_id');
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('dashboards');
        Schema::create('dashboards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title',60);
            $table->string('content')->nullable();
            $table->unsignedInteger('related_id');
            $table->char('type',1)
                ->comment('種類:1,スケジュール 2,案件通知,3,チャット');
            $table->char('pinned_mark',1)->comment('ピン止めマークを表示:1,固定表示,0,非固定表示')->nullable();
            $table->unsignedInteger('from_user_id');
            $table->unsignedInteger('to_user_id');
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }
}
