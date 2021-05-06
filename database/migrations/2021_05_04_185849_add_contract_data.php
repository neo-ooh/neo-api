<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContractData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("contracts", function (Blueprint $table) {
            $table->string("advertiser_name", 64)->after("owner_id");
            $table->string("executive_account_name", 64)->after("advertiser_name");
            $table->timestamp("start_date")->useCurrent()->after("executive_account_name");
            $table->timestamp("end_date")->useCurrent()->after("start_date");
            $table->json("data")->nullable()->after("end_date");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
