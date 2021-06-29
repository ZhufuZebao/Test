<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnterprisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enterprises', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50)->comment('会社名');
            $table->string('zip', 7)->comment('郵便番号');
            $table->string('pref', 20)->comment('都道府県');
            $table->string('town', 30)->comment('市区町村');
            $table->string('street', 20)->comment('番地');
            $table->string('house', 70)->nullable()->comment('建物名');
            $table->string('tel', 15)->comment('電話番号');
            $table->unsignedInteger('user_id')->comment('管理者ID');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users');
        });
        DB::statement("ALTER TABLE `enterprises` comment '事業者'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enterprises');
    }
}
