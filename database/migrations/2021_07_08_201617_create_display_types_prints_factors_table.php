<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\DisplayType;

return new class extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public string $tableName = "display_types_prints_factors";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->foreignId("display_type_id")->constrained("display_types");
            $table->foreignId("network_id")->constrained("networks");
            $table->unsignedSmallInteger("start_month");
            $table->unsignedSmallInteger("end_month");
            $table->unsignedDouble("product_exposure");
            $table->unsignedDouble("exposure_length");
            $table->unsignedSmallInteger("loop_length");
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
        Schema::dropIfExists($this->tableName);
    }
};
