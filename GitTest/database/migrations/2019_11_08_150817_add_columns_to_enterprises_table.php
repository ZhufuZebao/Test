<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToEnterprisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enterprises', function (Blueprint $table) {
            $table->smallInteger('plan')->default(0)->after('cooperator')
                ->comment('プラン:0,事業者以外,1,コミュニケーションプラン 2,スタンダードプラン');
            $table->Integer('amount')->nullable()->default(0)->after('plan')
                ->comment('契約ユーザ数');
            $table->double('storage',15,5)->nullable()->default(0)->after('amount')
                ->comment('ストレージ容量');
            $table->double('optionalStorage',15,5)->nullable()->default(0)->after('storage')
                ->comment('オプション容量');
            $table->double('usedStorage',15,5)->nullable()->default(0)->after('optionalStorage')
                ->comment('使用済み容量');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enterprises', function (Blueprint $table) {
            $table->dropColumn('plan');
            $table->dropColumn('amount');
            $table->dropColumn('storage');
            $table->dropColumn('optionalStorage');
            $table->dropColumn('usedStorage');
        });
    }
}
