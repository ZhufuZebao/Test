<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferRelatedTables extends Migration
{
    private $tbl = [
        'user_profiles',
        'skills',
        'user_skills',
        'qualifications',
        'user_qualifications',
        'job_vacancies',
        'job_offers',
        'job_offer_messages',
        'job_vacancy_statuses',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createUserProfile();

        $this->createSkill();
        $this->createUserSkill();

        $this->createQualification();
        $this->createUserQualification();

        $this->createJobVacancy(); // 求人情報マスター
        $this->createJobOffer(); // 個人に紐付いた求人
        $this->createJobOfferMessage();
        $this->createJobVacancyStatus();

        $this->insertMasterData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();

        // prepend `a foreign key constraint fails`
        while($tbl = array_pop($this->tbl))
        {
            Schema::dropIfExists($tbl);
        }
    }

    private function createUserProfile()
    {
        $tbl = $this->tbl[0];

        Schema::create($tbl, function (Blueprint $table) {
            $table->unsignedInteger('id')->primaryKey();
            $table->string('title',256);
            $table->text('content');
            $table->string('photo',256)->nullable(); // `basename.ext`
            $table->timestamps();

        });

    }

    private function createSkill()
    {
        $tbl = $this->tbl[1];

        Schema::create($tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',256);
            $table->timestamps();
        });
    }

    private function createUserSkill()
    {
        $tbl = $this->tbl[2];

        Schema::create($tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('skill_id');
            $table->timestamps();

        });
    }

    private function createQualification()
    {
        $tbl = $this->tbl[3];

        Schema::create($tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',256);
            $table->unsignedInteger('skill_id');
            $table->timestamps();

        });
    }

    private function createUserQualification()
    {
        $tbl = $this->tbl[4];

        Schema::create($tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('qualification_id');
            $table->timestamps();

        });
    }

    private function createJobVacancy()
    {
        $tbl = $this->tbl[5];

        Schema::create($tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',256);
            $table->text('description',256);
            $table->unsignedInteger('skill_id');
            $table->unsignedInteger('contractor_id');
            $table->unsignedInteger('user_id');
            $table->dateTime('st_date')->nullable();
            $table->dateTime('ed_date')->nullable();
            $table->unsignedInteger('status');
            $table->timestamps();

        });
    }

    private function createJobOffer()
    {
        $tbl = $this->tbl[6];

        Schema::create($tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

        });
    }

    private function createJobOfferMessage()
    {
        $tbl = $this->tbl[7];

        Schema::create($tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('vacancy_id');
            $table->unsignedInteger('sender_id');
            $table->text('content');
            $table->timestamps();

        });
    }

    private function createJobVacancyStatus()
    {
        $tbl = $this->tbl[8];

        Schema::create($tbl, function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
        });
    }

    private function insertMasterData()
    {
        $rows = [
            ['id' => 1 ,'name' => "土木"],
            ['id' => 2 ,'name' => "建築"],
            ['id' => 3 ,'name' => "大工"],
            ['id' => 4 ,'name' => "左官"],
            ['id' => 5 ,'name' => "とび"],
            ['id' => 6 ,'name' => "石工"],
            ['id' => 7 ,'name' => "内装"],
            ['id' => 8 ,'name' => "塗装"],
            ['id' => 9 ,'name' => "造園"],
        ];
        \App\Skill::insert($rows);

        $items = [
            "測量士","測量士補","発破技士","土木施工管理技士","建築施工管理技士","建設機械施工技士","車両系建設機械技能者","ショベルローダー運転技能者","フォークリフト運転技能者","クレーン・デリック運転士","移動式クレーン運転士","床上操作式クレーン限定","クレーン限定","玉掛作業者","管工事施工管理技士","建築設備士","建築設備検査資格者","特殊建築設備調査資格者","昇降機検査資格者","消防設備士","消防設備点検資格者","衛生管理者","衛生工学衛生管理者","作業環境測定士","林業架線作業主任者","砂利採取業務主任者","採石業務管理者","採石掘削作業主任者","地山・土止支保作業主任者","酸欠・硫化水素危険作業","コンクリート物解体作業主任","コンクリート破砕器作業主任","型枠支保工組立作業主任者","足場組立作業主任者","建築物鉄骨組立作業主任者","木造建築組立業主任者","ガス溶接作業主任者","危険物取扱者","第1種圧力容器取扱主任者","ボイラー技士","ボイラー整備士","ボイラー溶接士","小規模ボイラー取扱者","ガス主任技術者","高圧ガス製造保安責任者","高圧ガス販売主任者","高圧ガス移動監視者","特定高圧ガス取扱主任者","液化石油ガス設備士","高圧室内作業主任者","酸素欠乏危険作業主任者","エックス線作業主任者","ガンマ線写真撮影作業主任","火薬類取扱保安責任者","火薬類製造保安責任者","自動車整備士","有機溶剤作業主任者","給水装置工事主任技術者","石綿作業主任者","鉛作業主任者","高所作業車運転技能講習","木材加工用機械作業主任者","乾燥設備作業主任者","ダム管理主任技術者","ダム水路主任技術者","ずい道等掘削等作業主任者","ずい道等覆工作業主任者","プレス機械作業主任者","はい作業主任者","警備業務検定","ガス溶接技能者","ガス消費機器設置工事監督","鋼橋架設等作業主任者","コンクリート橋架設作業主任","毒物劇物取扱責任者","ﾎﾞｲﾗｰ･ﾀｰﾋﾞﾝ主任技術者","機械警備業務管理者","貯水槽清掃作業監督者","清掃作業監督者","ダクト清掃作業監督者","排水管清掃作業監督者","防除作業監督者","統括管理者","空調給排水管理監督者","防火対象物点検資格者","防火管理者","防災管理点検資格者","防災管理者","アーク溶接作業者","ジャッキ式つり上げ機械運転","プレス金型取替作業者","ボーリングマシン運転者","刈払機取扱作業者","巻上げ機（ウィンチ）運転者","家畜人工授精師","ピアノ調律技能士","解体工事施工技士","不整地運搬車運転者","安全衛生推進者","警戒業務管理者・警戒要員","ゴンドラ操作者","基礎施工士","圧入施工技士","商業施設士","配電制御システム検査技士","コンクリート切断穿孔技能審査","窯業系サイディング施工士","倉庫管理主任者","屋外広告士","建築物石綿含有建材調査者","予防技術検定","自衛消防技術試験","コンクリート技士","コンクリート主任技士","PC工法溶接管理技術者","冷凍空調技士","コンクリート診断士","食品冷凍技士","地質調査技士","自転車技士","自転車安全整備士","水路測量技術検定","設備士資格検定","鉄骨製作管理技術者","日本農業技術検定","舗装施工管理技術者"
        ];

        $rows = [];
        foreach($items as $item)
        {
            $rows[] = ['skill_id' => 2, 'name' => $item];
        }
        \App\Qualification::insert($rows);

    }

}
