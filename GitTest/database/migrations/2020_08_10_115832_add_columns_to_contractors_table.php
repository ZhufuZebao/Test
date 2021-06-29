<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToContractorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('contractors', function (Blueprint $table) {
            //drop
            $table->dropColumn('addr');
            $table->dropColumn('establishment');
            $table->dropColumn('representative');
            $table->dropColumn('contents');
            //add
            $table->string('town',30)->nullable()->after('pref')->comment('市区町村');
            $table->string('street',20)->nullable()->after('town')->comment('番地');
            $table->string('house',70)->nullable()->after('street')->comment('建物名');
            $table->string('tel',15)->nullable()->after('house')->comment('電話番号');
            $table->unsignedInteger('enterprise_id')->after('tel')->comment('会社ID');
            $table->string('people',30)->after('enterprise_id')->comment('契約者名');
            $table->timestamp('deleted_at')->nullable();
            //modify
            $table->string('pref', 20)->nullable()->change();
            $table->string('name', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropColumn('town');
            $table->dropColumn('street');
            $table->dropColumn('house');
            $table->dropColumn('tel');
            $table->dropColumn('enterprise_id');
            $table->dropColumn('people');
            $table->dropColumn('deleted_at');
            $table->unsignedInteger('pref')->change();
            $table->string('name', 128)->change();

            $table->string('addr',190)->after('pref');
            $table->date('establishment')->after('pref');
            $table->string('representative',100)->after('establishment');
            $table->text('contents')->nullable();
        });
    }
}
