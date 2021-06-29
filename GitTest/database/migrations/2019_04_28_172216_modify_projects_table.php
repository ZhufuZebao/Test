<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            //カラムを削除
            $table->dropColumn('name');
            $table->dropColumn('contractor_id');
            $table->dropColumn('st_date');
            $table->dropColumn('ed_date');
            $table->dropColumn('manager');
            $table->dropColumn('author');
            $table->dropColumn('create_date');
            $table->dropColumn('photo');
        });
        Schema::table('projects', function (Blueprint $table) {
            //カラムを追加
            $table->string('place_name', 50)->comment('物件・現場名')->after('id');
            $table->string('construction_name', 50)->comment('工事件名')->after('place_name');
            $table->string('zip', 7)->comment('郵便番号')->after('construction_name');
            $table->string('pref', 20)->comment('都道府県')->after('zip');
            $table->string('town', 50)->comment('区市町村')->after('pref');
            $table->string('street', 50)->comment('番地')->after('town');
            $table->string('house', 50)->nullable()->comment('建物名')->after('street');
            $table->string('address', 170)->comment('案件場所')->after('house');
            $table->string('longitude', 12)->nullable()->comment('経度:保留')->after('address');
            $table->string('latitude', 12)->nullable()->comment('緯度:保留')->after('longitude');
            $table->string('tel', 15)->comment('電話番号')->after('latitude');
            $table->string('fax', 15)->nullable()->comment('FAX')->after('tel');
            $table->string('construction_type', 1)->nullable()->comment('工事種類:1,改築　2,改築')->after('fax');

            $table->string('mng_company_name', 50)->nullable()->comment('管理会社　名')->after('construction_type');
            $table->string('mng_company_address', 70)->nullable()->comment('管理会社　住所')->after('mng_company_name');
            $table->string('mng_company_tel', 15)->nullable()->comment('管理会社　電話')->after('mng_company_address');
            $table->string('mng_company_chief', 20)->nullable()->comment('管理会社　担当者')->after('mng_company_tel');
            $table->string('mng_company_chief_position', 20)->nullable()->comment('管理会社　担当者役職')->after('mng_company_chief');
            $table->string('realtor_name', 50)->nullable()->comment('不動産屋　名')->after('mng_company_chief_position');
            $table->string('realtor_address', 70)->nullable()->comment('不動産屋　住所')->after('realtor_name');
            $table->string('realtor_tel', 15)->nullable()->comment('不動産屋　電話')->after('realtor_address');
            $table->string('realtor_chief', 20)->nullable()->comment('不動産屋　担当者')->after('realtor_tel');
            $table->string('realtor_chief_position', 20)->nullable()->comment('不動産屋　担当者役職')->after('realtor_chief');
            $table->string('site_area', 20)->nullable()->comment('物件規模　敷地面積')->after('realtor_chief_position');
            $table->string('floor_area', 20)->nullable()->comment('物件規模　建物面積')->after('site_area');
            $table->string('floor_numbers', 3)->nullable()->comment('物件規模　階数')->after('floor_area');
            $table->string('subject_image', 150)->nullable()->comment('案件画像')->after('floor_numbers');
            $table->string('building_type', 20)->nullable()->comment('建物用途')->after('subject_image');
            $table->date('st_date')->nullable()->comment('工事期間　着工日')->after('building_type');
            $table->date('ed_date')->nullable()->comment('工事期間　竣工日')->after('st_date');
            $table->date('open_date')->nullable()->comment('工事期間オープン予定日')->after('ed_date');
            $table->string('construction_company', 30)->nullable()->comment('工事会社')->after('open_date');
            $table->text('construction_special_content')->nullable()->comment('工事に伴う特記事項')->after('construction_company');
            $table->string('progress_status', 1)->nullable()->comment('進捗状況:1,受注前 2,着工前 3,先行 4順調 5,多少の遅れあり 6,大幅な遅れ 7,竣工間近 8,竣工')->after('construction_special_content');
            $table->text('progress_special_content')->nullable()->comment('進捗特記事項')->after('progress_status');
            $table->string('security_management_tel', 15)->nullable()->comment('安全管理　緊急連絡先')->after('progress_special_content');
            $table->string('security_management_chief', 20)->nullable()->comment('安全管理　現場責任者')->after('security_management_tel');
            $table->string('security_management_deputy', 20)->nullable()->comment('安全管理　現場副責任者')->after('security_management_chief');

            $table->string('fire_station_name', 70)->nullable()->comment('安全管理　管轄消防署名')->after('security_management_deputy');
            $table->string('fire_station_chief', 20)->nullable()->comment('安全管理　管轄消防署担当者')->after('fire_station_name');
            $table->string('fire_station_tel', 15)->nullable()->comment('安全管理　管轄消防電話')->after('fire_station_chief');
            $table->string('police_station_name', 70)->nullable()->comment('安全管理　管轄警察署名')->after('fire_station_tel');
            $table->string('police_station_chief', 20)->nullable()->comment('安全管理　管轄警察署担当者')->after('police_station_name');
            $table->string('police_station_tel', 15)->nullable()->comment('安全管理　管轄警察電話')->after('police_station_chief');
            $table->unsignedInteger('customer_id')->nullable()->comment('施主ID')->after('police_station_tel');
            $table->unsignedInteger('customer_office_id')->nullable()->comment('施主事業所ID')->after('customer_id');

            $table->unsignedInteger('created_by')->comment('作成者')->after('customer_office_id');
            $table->unsignedInteger('updated_by')->comment('更新者')->after('created_by');
            $table->softDeletes()->after('updated_at');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('customer_office_id')->references('id')->on('customer_offices');
        });
        DB::statement("ALTER TABLE `projects` comment '案件'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            //カラムを削除
            $table->dropColumn('place_name');
            $table->dropColumn('construction_name');
            $table->dropColumn('zip');
            $table->dropColumn('pref');
            $table->dropColumn('town');
            $table->dropColumn('street');
            $table->dropColumn('house');
            $table->dropColumn('address');
            $table->dropColumn('longitude');
            $table->dropColumn('latitude');
            $table->dropColumn('tel');
            $table->dropColumn('fax');
            $table->dropColumn('construction_type');

            $table->dropColumn('mng_company_name');
            $table->dropColumn('mng_company_address');
            $table->dropColumn('mng_company_tel');
            $table->dropColumn('mng_company_chief');
            $table->dropColumn('mng_company_chief_position');
            $table->dropColumn('realtor_name');
            $table->dropColumn('realtor_address');
            $table->dropColumn('realtor_tel');
            $table->dropColumn('realtor_chief');
            $table->dropColumn('realtor_chief_position');
            $table->dropColumn('site_area');
            $table->dropColumn('floor_area');
            $table->dropColumn('floor_numbers');
            $table->dropColumn('subject_image');
            $table->dropColumn('building_type');
            $table->dropColumn('st_date');
            $table->dropColumn('ed_date');
            $table->dropColumn('open_date');
            $table->dropColumn('construction_company');
            $table->dropColumn('construction_special_content');
            $table->dropColumn('progress_status');
            $table->dropColumn('progress_special_content');
            $table->dropColumn('security_management_tel');
            $table->dropColumn('security_management_chief');
            $table->dropColumn('security_management_deputy');

            $table->dropColumn('fire_station_name');
            $table->dropColumn('fire_station_chief');
            $table->dropColumn('fire_station_tel');
            $table->dropColumn('police_station_name');
            $table->dropColumn('police_station_chief');
            $table->dropColumn('police_station_tel');

            $table->dropColumn('customer_id');
            $table->dropColumn('customer_office_id');
            $table->dropColumn('deleted_at');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });

        Schema::table('projects', function (Blueprint $table) {
            //カラムを戻す
            $table->string('name', 128)->after('id');
            $table->unsignedInteger('contractor_id')->after('name');
            $table->date('st_date')->after('contractor_id');
            $table->date('ed_date')->after('st_date');
            $table->string('manager', 32)->nullable()->after('ed_date');
            $table->string('author', 32)->nullable()->after('manager');
            $table->date('create_date')->nullable()->after('author');;
            $table->string('photo')->nullable()->after('create_date');;
        });
    }
}
