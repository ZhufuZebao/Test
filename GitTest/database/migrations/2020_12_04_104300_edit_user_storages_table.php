<?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class EditUserStoragesTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::dropIfExists('user_storages');
            Schema::create('user_storages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->integer('group_id')->nullable();
                $table->integer('enterprise_id')->nullable()->comment("自分会社");
                $table->integer('other_enterprise_id')->nullable()->comment("容量会社");
                $table->integer('project_id')->nullable();
                $table->double('doc_storage',15,5)->nullable()->default(0);
                $table->double('chat_storage',15,5)->nullable()->default(0);
                $table->double('chat_file_storage',15,5)->nullable()->default(0);
                $table->integer('type')->default(1)->comment("種類:1,社内  2,協力会社 3,職人 4,案件");
                $table->timestamps();
                $table->softDeletes();
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('user_storages');
            Schema::create('user_storages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->double('doc_storage',15,5)->nullable()->default(0);
                $table->double('chat_storage',15,5)->nullable()->default(0);
                $table->double('chat_file_storage',15,5)->nullable()->default(0);
                $table->timestamps();
            });
        }
    }
