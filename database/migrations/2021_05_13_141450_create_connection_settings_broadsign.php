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
    public string $tableName = "connection_settings_broadsign";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->foreignId("connection_id")->primary()->constrained("broadcasters_connections")->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger("domain_id");
            $table->unsignedBigInteger("default_customer_id")->nullable();
            $table->unsignedBigInteger("default_tracking_id")->nullable();
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