<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('worker', 1)->default('1')->after('birth')->comment('職人フラグ:1,職人 0,職人以外');
            $table->string('enterprise_admin', 1)->nullable()->after('worker')->comment('事業者種類:0,一般アカウント 1,管理アカウント');
            $table->unsignedInteger('coop_enterprise_id')->nullable()->after('enterprise_id')->comment('協力会社ID');
            $table->char('company_div', 1)->nullable()->after('things_to_realize')->comment('会社区分 1=法人、2=個人');
            $table->char('corporate_type', 1)->nullable()->after('company_div')->comment('会社タイプ　1=株式会社、2=有限会社、3=合同会社');
            $table->char('corporate_name_position', 1)->nullable()->after('corporate_type')->comment('会社名位置　1=前、2=後');
            $table->text('company_name')->nullable()->after('corporate_name_position')->comment('会社名 個人の場合は個人会社名');
            $table->timestamp('deleted_at')->nullable()->after('company_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('worker');
            $table->dropColumn('enterprise_admin');
            $table->dropColumn('coop_enterprise_id');
            $table->dropColumn('company_div');
            $table->dropColumn('corporate_type');
            $table->dropColumn('corporate_name_position');
            $table->dropColumn('company_name');
            $table->dropColumn('deleted_at');
        });
    }
}
