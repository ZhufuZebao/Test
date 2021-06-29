<?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class ModifyTitleToDashboardsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::table('dashboards', function (Blueprint $table) {
                $table->string('title', 500)->change();
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::table('dashboards', function (Blueprint $table) {
                $table->string('title', 60)->change();
            });
        }
    }
