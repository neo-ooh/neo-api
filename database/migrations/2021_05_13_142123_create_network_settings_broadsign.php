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
    public string $tableName = "network_settings_broadsign";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->foreignId("network_id")->primary()->constrained("networks")->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger("customer_id");
            $table->unsignedBigInteger("container_id");
            $table->unsignedBigInteger("tracking_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists($this->tableName);
    }
};
