<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createTableEstate();
        $this->createTableEstateProgress();
        $this->insertTableEstateProgress();
        $this->createTableJobType();
        $this->insertTableJobType();
        $this->createTableEstateJob();
        $this->createTableEstateHospital();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estate_jobs');
        Schema::dropIfExists('job_types');
        Schema::dropIfExists('estate_hospitals');
        Schema::dropIfExists('estate_progresses');
        Schema::dropIfExists('estates');
    }

    private function createTableEstate()
    {
        Schema::create('estates', function (Blueprint $table) {
            $table->increments('id');

            $table->string('location_name',256)->comment('物件・現場名');
            $table->string('project_name',256)->comment('工事件名');
            $table->string('location_zip',256)->comment('物件・現場住所郵便番号');
            $table->unsignedInteger('location_pref_id')->comment('都道府県');
            $table->string('location_town',256)->comment('市区町村');
            $table->string('location_street',256)->comment('番地');
            $table->string('location_house',256)->nullable()->comment('建物名');
            $table->string('location_tel',256)->nullable()->comment('電話番号');
            $table->unsignedInteger('renovate_flg')->default(0)->comment('新装/改築');
            $table->string('staff_name',256)->nullable()->comment('現場担当者氏名');
            $table->string('staff_position',256)->nullable()->comment('担当者役職');
            $table->string('staff_tel',256)->nullable()->comment('携帯電話番号');
            $table->string('staff_email',256)->nullable()->comment('メールアドレス');
            $table->string('maintainer_company',256)->nullable()->comment('管理会社名称');
            $table->string('maintainer_zip',256)->nullable()->comment('管理会社住所（上記物件住所と同様の構成）');
            $table->unsignedInteger('maintainer_pref_id')->nullable()->comment('管理会社住所（上記物件住所と同様の構成）');
            $table->string('maintainer_town',256)->nullable()->comment('管理会社住所（上記物件住所と同様の構成）');
            $table->string('maintainer_street',256)->nullable()->comment('管理会社住所（上記物件住所と同様の構成）');
            $table->string('maintainer_house',256)->nullable()->comment('管理会社住所（上記物件住所と同様の構成）');
            $table->string('maintainer_tel',256)->nullable()->comment('管理会社電話番号');
            $table->string('maintainer_person',256)->nullable()->comment('管理会社担当者氏名');
            $table->string('maintainer_position',256)->nullable()->comment('管理会社担当者役職');
            $table->string('realtor_company',256)->nullable()->comment('不動産屋名称');
            $table->string('realtor_zip',256)->nullable()->comment('不動産屋住所（上記物件住所と同様の構成）');
            $table->unsignedInteger('realtor_pref_id')->nullable()->comment('住所（上記物件住所と同様の構成）');
            $table->string('realtor_town',256)->nullable()->comment('不動産屋住所（上記物件住所と同様の構成）');
            $table->string('realtor_street',256)->nullable()->comment('不動産屋住所（上記物件住所と同様の構成）');
            $table->string('realtor_house',256)->nullable()->comment('不動産屋住所（上記物件住所と同様の構成）');
            $table->string('realtor_tel',256)->nullable()->comment('不動産屋電話番号');
            $table->string('realtor_person',256)->nullable()->comment('不動産屋担当者氏名');
            $table->string('realtor_position',256)->nullable()->comment('不動産屋担当者役職');
            $table->string('land_area',256)->nullable()->comment('物件規模敷地面積');
            $table->string('floor_area',256)->comment('建物面積（延べ床面積）');
            $table->unsignedInteger('floor_level')->comment('階数');
            $table->string('usage',256)->nullable()->comment('用途');
            $table->date('start_at')->nullable()->comment('工事期間着工');
            $table->date('finish_at')->nullable()->comment('竣工');
            $table->date('open_at')->nullable()->comment('オープン予定日');
            $table->unsignedInteger('contractor_id')->nullable()->comment('工事会社');
            $table->string('note',256)->nullable()->comment('工事に伴う特記事項');
            $table->string('progress_id',256)->nullable()->comment('進捗状況');
            $table->string('comment',256)->nullable()->comment('進捗特記事項');
            $table->string('sos_tel',256)->nullable()->comment('安全管理情報緊急時連絡先電話番号');
            $table->string('chief',256)->nullable()->comment('現場責任者');
            $table->string('assistant',256)->nullable()->comment('現場副責任者');
            $table->string('firehouse_name',256)->nullable()->comment('管轄消防署名');
            $table->string('firehouse_person',256)->nullable()->comment('管轄消防署担当者名');
            $table->string('firehouse_tel',256)->nullable()->comment('管轄消防署電話番号');
            $table->string('police_name',256)->nullable()->comment('管轄警察署名');
            $table->string('police_person',256)->nullable()->comment('管轄警察署担当者名');
            $table->string('police_tel',256)->nullable()->comment('管轄警察署電話番号');
            $table->timestamps();
            $table->unsignedInteger('user_id')->comment("作成者");

        });
    }

    private function createTableEstateJob()
    {
        Schema::create('estate_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('estate_id');
            $table->unsignedInteger('job_type_id');
            $table->string('chief',256)->default('未定');
            $table->string('chief_tel',256)->nullable();
            $table->timestamps();
            $table->unsignedInteger('user_id')->comment("作成者");

        });
    }

    private function createTableEstateHospital()
    {
        Schema::create('estate_hospitals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('estate_id');
            $table->unsignedInteger('user_id');
            $table->string('name',256);
            $table->string('tel',256);
            $table->timestamps();

        });
    }

    private function createTableEstateProgress()
    {

        Schema::create('estate_progresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',256)->comment('進捗状況');
            $table->timestamps();
        });
    }

    private function createTableJobType()
    {
        Schema::create('job_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',256)->comment('工種');
            $table->timestamps();
        });
    }
    
    private function insertTableJobType()
    {
        DB::table('job_types')->insert([
            'name'       => "電気"
        ]);
        DB::table('job_types')->insert([
            'name'       => "ガス"
        ]);
        DB::table('job_types')->insert([
            'name'       => "水道"
        ]);
        DB::table('job_types')->insert([
            'name'       => "空調"
        ]);
        DB::table('job_types')->insert([
            'name'       => "その他"
        ]);
        DB::table('job_types')->update([
            'created_at'       => DB::raw('CURRENT_TIMESTAMP()'),
            'updated_at'       => DB::raw('CURRENT_TIMESTAMP()'),
        ]);
    }

    private function insertTableEstateProgress()
    {
        DB::table('estate_progresses')->insert([
            'name'       => "先行",
        ]);
        DB::table('estate_progresses')->insert([
            'name'       => "順調",
        ]);
        DB::table('estate_progresses')->insert([
            'name'       => "多少の遅れあり",
        ]);
        DB::table('estate_progresses')->insert([
            'name'       => "大幅な遅れ",
        ]);
        DB::table('estate_progresses')->insert([
            'name'       => "竣工間近",
        ]);
        DB::table('estate_progresses')->insert([
            'name'       => "竣工",
        ]);
        DB::table('estate_progresses')->update([
            'created_at'       => DB::raw('CURRENT_TIMESTAMP()'),
            'updated_at'       => DB::raw('CURRENT_TIMESTAMP()'),
        ]);
    }

}
