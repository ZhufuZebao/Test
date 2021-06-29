<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnterpriseParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enterprise_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('enterprise_id')->comment('会社ID');
            $table->unsignedInteger('user_id')->nullable()->comment('関係者ID');
            $table->string('email', 100)->comment('招待メールアドレス');
            $table->string('invitation_code', 50)->comment('招待コード');
            $table->text('message')->nullable()->comment('招待メッセージ');
            $table->unsignedTinyInteger('agree')->default(0)->comment('承認ステータス:0,承認待ち 1,承認済み');
            $table->unsignedInteger('created_by')->comment('作成者');
            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('users');
            $table->index(['enterprise_id', 'user_id']);
        });
        DB::statement("ALTER TABLE `enterprise_participants` comment '事業関係者'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enterprise_participants');
    }
}
