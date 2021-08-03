<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public string $tableName = "contracts_networks_data";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->foreignId("contract_id")->primary()->constrained("contracts");
            $table->string("network", 16);
            $table->boolean("has_guaranteed_reservations")->nullable();
            $table->unsignedBigInteger("guaranteed_impressions")->nullable();
            $table->unsignedBigInteger("guaranteed_media_value")->nullable();
            $table->unsignedBigInteger("guaranteed_net_investment")->nullable();
            $table->boolean("has_bonus_reservations")->nullable();
            $table->unsignedBigInteger("bonus_impressions")->nullable();
            $table->unsignedBigInteger("bonus_media_value")->nullable();
            $table->boolean("has_bua_reservations")->nullable();
            $table->unsignedBigInteger("bua_impressions")->nullable();
            $table->unsignedBigInteger("bua_media_value")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
};
