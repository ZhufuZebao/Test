<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_roles', function (Blueprint $table) {
            $table->Increments('id');
            $table->string('name',256)->comment('担当区分');
            $table->timestamps();
        });
        $this->insertTableCustomerRoles();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_roles');
    }

    private function insertTableCustomerRoles()
    {
        DB::table('customer_roles')->insert([
            'name'       => "社長"
        ]);
        DB::table('customer_roles')->insert([
            'name'       => "営業担当"
        ]);
        DB::table('customer_roles')->insert([
            'name'       => "運営担当"
        ]);
        DB::table('customer_roles')->insert([
            'name'       => "その他"
        ]);
        DB::table('customer_roles')->update([
            'created_at'       => DB::raw('CURRENT_TIMESTAMP()'),
            'updated_at'       => DB::raw('CURRENT_TIMESTAMP()'),
        ]);
    }
}
