<?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class CreateUserStoragesTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('user_storages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->double('doc_storage',15,5)->nullable()->default(0);
                $table->double('chat_storage',15,5)->nullable()->default(0);
                $table->double('chat_file_storage',15,5)->nullable()->default(0);

                $table->timestamps();

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
        }
    }
