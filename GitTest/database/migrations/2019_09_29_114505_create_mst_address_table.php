<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMstAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mst_address', function (Blueprint $table) {
            $table->char('officialCode', 11)->comment('全国地方公共団体コード:(JIS X0401、X0402)');
            $table->char('postalCode5', 5)->comment('(旧)郵便番号(5桁)');
            $table->char('postalCode7', 7)->comment('郵便番号(7桁)');
            $table->string('kana_pref', 20)->comment('都道府県名（カタカナ）');
            $table->string('kana_city', 40)->comment('市区町村名（カタカナ）');
            $table->string('kana_town', 40)->comment('町域名（カタカナ）');
            $table->string('pref', 20)->comment('都道府県名');
            $table->string('city', 40)->comment('市区町村名');
            $table->string('town', 40)->comment('町域名');
            $table->boolean('flag_doubleCode')->comment('一町域が二以上の郵便番号で表される場合の表示:　(注3)　(「1」は該当、「0」は該当せず)');
            $table->boolean('flag_banchi')->comment('小字毎に番地が起番されている町域の表示:　(注4)　(「1」は該当、「0」は該当せず)');
            $table->boolean('flag_chome')->comment('丁目を有する町域の場合の表示:　(「1」は該当、「0」は該当せず)');
            $table->boolean('flag_double_area')->comment('一つの郵便番号で二以上の町域を表す場合の表示:　(注5)　(「1」は該当、「0」は該当せず)');
            $table->integer('flag_update')->comment('更新の表示:（注6）（「0」は変更なし、「1」は変更あり、「2」廃止（廃止データのみ使用））');
            $table->integer('flag_update_reason')->comment('変更理由:　(「0」は変更なし、「1」市政・区政・町政・分区・政令指定都市施行、「2」住居表示の実施、「3」区画整理、「4」郵便区調整等、「5」訂正、「6」廃止(廃止データのみ使用))');
            $table->index(['postalCode5', 'postalCode7']);
        });
        DB::statement('ALTER TABLE mst_address MODIFY COLUMN flag_update INT(1) NOT NULL');
        DB::statement('ALTER TABLE mst_address MODIFY COLUMN flag_update_reason INT(1) NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mst_address');
    }
}
