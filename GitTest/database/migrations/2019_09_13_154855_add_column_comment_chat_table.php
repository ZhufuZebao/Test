<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCommentChatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatcontacts', function (Blueprint $table) {
            $table->increments('id')
                    ->comment('ID（自動採番）')->change();
            $table->unsignedInteger('from_user_id')
                    ->comment('依頼元ユーザーID')->change();
            $table->unsignedInteger('to_user_id')->nullable()
                    ->comment('依頼先ユーザーID')->change();
            $table->string('email')->nullable()
                    ->comment('メールアドレス')->change();
            $table->datetime('contact_date')
                    ->comment('ID（自動採番）')->change();
            $table->text('contact_message')->nullable()
                    ->comment('依頼時のメッセージ')->change();
            $table->text('agree_message')->nullable()
                    ->comment('承認時のメッセージ')->change();
            //$table->char('contact_agree', 1)
            //        ->comment('0=依頼のみ、1=承認、2=拒否')->change();

            DB::statement("ALTER TABLE chatcontacts MODIFY contact_agree CHAR(1) NOT NULL COMMENT '0=依頼のみ、1=承認、2=拒否';");
        });

        Schema::table('chatgroups', function(Blueprint $table)
        {
            $table->increments('id')
                    ->comment('チャットグループID（自動採番）')->change();
            $table->unsignedInteger('user_id')
                    ->comment('ユーザーID')->change();
            $table->unsignedInteger('group_id')
                    ->comment('グループID')->change();
            $table->smallInteger('admin')
                    ->comment('1=管理者, 0=メンバー')->change();

            /*
            DB::statement("ALTER TABLE chatgroups MODIFY id int(10) unsigned NOT NULL COMMENT 'チャットグループID（自動採番）';");
            DB::statement("ALTER TABLE chatgroups MODIFY user_id int(10) unsigned NOT NULL COMMENT 'ユーザーID';");
            DB::statement("ALTER TABLE chatgroups MODIFY group_id int(10) unsigned NOT NULL COMMENT 'グループID';");
            DB::statement("ALTER TABLE chatgroups MODIFY admin smallint(6) NOT NULL COMMENT '1=管理者, 0=メンバー';");
            */
        });

        Schema::table('chatlastreads', function (Blueprint $table) {
            $table->increments('id')
                    ->comment('ID（自動採番）')->change();
            $table->unsignedInteger('group_id')
                    ->comment('グループID')->change();
            $table->unsignedInteger('user_id')
                    ->comment('ユーザーID')->change();
            $table->unsignedInteger('message_id')
                    ->comment('最後に見たメッセージのID')->change();
        });

        Schema::table('chatmessages', function (Blueprint $table) {
            $table->increments('id')
                    ->comment('メッセージID（自動採番）')->change();
            $table->unsignedInteger('group_id')
                    ->comment('グループID')->change();
            $table->unsignedInteger('from_user_id')
                    ->comment('メッセージを送信したユーザーのID')->change();
            $table->text('message')->nullable()
                    ->comment('メッセージ')->change();
            $table->text('file_name')->nullable()
                    ->comment('添付ファイル名')->change();
            $table->unsignedInteger('task_id')->nullable()
                    ->comment('タスクID')->change();
        });

        Schema::table('chatpersons', function (Blueprint $table) {
            $table->increments('id')
                    ->comment('ID（自動採番）')->change();
            $table->unsignedInteger('group_id')
                    ->comment('グループID')->change();
            $table->unsignedInteger('user_id')
                    ->comment('TO、REのユーザーID')->change();
            $table->unsignedInteger('from_user_id')
                    ->comment('送信したユーザーのID')->change();
            $table->unsignedInteger('message_id')
                    ->comment('メッセージID')->change();
            $table->unsignedInteger('re_message_id')->nullable()
                    ->comment('返信元のメッセージID')->change();
            $table->integer('flag')
                    ->comment('1=TO、2=RE')->change();
        });

        Schema::table('chattaskcharges', function (Blueprint $table) {
            $table->increments('id')
                    ->comment('ID（自動採番）')->change();
            $table->unsignedInteger('task_id')->nullable()
                    ->comment('タスクID')->change();
            $table->unsignedInteger('user_id')->nullable()
                    ->comment('ユーザーID')->change();
        });

        Schema::table('chattasks', function (Blueprint $table) {
            $table->increments('id')
                    ->comment('タスクID（自動採番）')->change();
            $table->unsignedInteger('group_id')->nullable()
                    ->comment('グループID')->change();
            $table->unsignedInteger('create_user_id')->nullable()
                    ->comment('作成者のユーザーID')->change();
            $table->datetime('limit_date')->nullable()
                    ->comment('有効期限')->change();
            $table->text('note')->nullable()
                    ->comment('タスクの内容')->change();
            $table->date('complete_date')
                    ->comment('完了日')->change();
//            $table->integer('notify')->nullable()
//                    ->comment('通知設定（NULL=通知なし、0=期限の時刻, 5=5分前, 10=10分前, 30=30分前, 60=１時間前, 1440=前日）')->change();
        });

        Schema::table('groups', function(Blueprint $table){
            $table->increments('id')
                    ->comment('グループID（自動採番）')->change();
            $table->string('name')
                    ->comment('グループ名')->change();
            $table->smallInteger('kind')
                    ->comment('0=グループチャット、1=ダイレクトチャット')->change();// 0:ダイレクト, 1:グループ
            $table->string('file',64)->nullable()
                    ->comment('プロフィール画像のファイル名')->change();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->increments('id')
                    ->comment('ID（自動採番）')->change();
            $table->unsignedInteger('user_id')->nullable()
                    ->comment('ユーザーID')->change();
            //$table->double('latitude', 10, 7)
            //        ->comment('緯度')->change(); // 緯度
            //$table->double('longitude', 10, 7)
            //        ->comment('経度')->change(); // 軽度
            //$table->double('altitude', 9, 6)
            //        ->comment('高度')->change(); // 高度

            DB::statement("ALTER TABLE locations MODIFY latitude double(10, 7) NOT NULL COMMENT '緯度';");
            DB::statement("ALTER TABLE locations MODIFY longitude double(10, 7) NOT NULL COMMENT '経度';");
            DB::statement("ALTER TABLE locations MODIFY altitude double(9, 6) NOT NULL COMMENT '高度';");
        });

        Schema::table('prefs', function (Blueprint $table) {
            $table->increments('id')
                    ->comment('ID（自動採番）')->change();
            $table->string('name',12)
                    ->comment('都道府県名')->change();
            $table->string('order',12)
                    ->comment('並び順')->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*
        Schema::table('chatcontacts', function(Blueprint $table) {
            $table->increments('id')->change();
            $table->unsignedInteger('from_user_id')->change();
            $table->unsignedInteger('to_user_id')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->datetime('contact_date')->change();
            $table->text('contact_message')->nullable()->change();
            $table->text('agree_message')->nullable()->change();
            $table->char('contact_agree', 1)->change();
        });

        Schema::table('chatgroups', function(Blueprint $table)
        {
            $table->increments('id')->change();
            $table->unsignedInteger('user_id')->change();
            $table->unsignedInteger('group_id')->change();
            $table->smallInteger('admin')->change();  // 1:管理者権限, 0:メンバー
        });

        Schema::table('chatlastreads', function (Blueprint $table) {
            $table->increments('id')->change();
            $table->unsignedInteger('group_id')->change();
            $table->unsignedInteger('user_id')->change();
            $table->unsignedInteger('message_id')->change();
        });

        Schema::table('chatmessages', function (Blueprint $table) {
            $table->increments('id')->change();
            $table->unsignedInteger('group_id')->change();
            $table->unsignedInteger('from_user_id')->change();
            $table->text('message')->nullable()->change();
            $table->text('file_name')->nullable()->change();
            $table->unsignedInteger('task_id')->nullable()->after('file_name')->change();
        });

        Schema::table('chatpersons', function (Blueprint $table) {
            $table->increments('id')->change();
            $table->unsignedInteger('group_id')->change();
            $table->unsignedInteger('user_id')->change();
            $table->unsignedInteger('from_user_id')->change();
            $table->unsignedInteger('message_id')->change();
            $table->unsignedInteger('re_message_id')->nullable()->change();
            $table->integer('flag')->change();// 1:TO 2:RE
        });

        Schema::table('chattaskcharges', function (Blueprint $table) {
            $table->increments('id')->change();
            $table->unsignedInteger('task_id')->nullable()->change();
            $table->unsignedInteger('user_id')->nullable()->change();
        });

        Schema::table('chattasks', function (Blueprint $table) {
            $table->increments('id')->change();
            $table->unsignedInteger('group_id')->nullable()->change();
            $table->unsignedInteger('create_user_id')->nullable()->change();
            $table->date('limit_date')->nullable()->change();
            $table->text('note')->nullable()->change();
            $table->date('complete_date')->change();
            $table->integer('notify')->nullable()->change();
        });

        Schema::table('groups', function(Blueprint $table){
            $table->increments('id')->change();
            $table->string('name')->change();
            $table->smallInteger('kind')->change();   // 0:ダイレクト, 1:グループ
            $table->string('file',64)->nullable()->change();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->increments('id')->change();
            $table->unsignedInteger('user_id')->nullable()->change();
            $table->double('latitude', 10, 7)->change();  // 緯度
            $table->double('longitude', 10, 7)->change(); // 軽度
            $table->double('altitude', 9, 6)->change();   // 高度
        });

        Schema::table('prefs', function (Blueprint $table) {
            $table->increments('id')->change();
            $table->string('name',12)->change();
            $table->string('order',12)->change();
        });
        */
    }
}
