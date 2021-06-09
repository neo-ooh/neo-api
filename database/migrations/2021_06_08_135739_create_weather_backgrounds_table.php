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
    public string $tableName = "weather_backgrounds";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string("weather", 32)->nullable();
            $table->set("period", ["ALL", "MORNING", "DAY", "DUSK", "NIGHT", "RANDOM"]);
            $table->foreignId("weather_location_id")->constrained("weather_locations");
            $table->foreignId("format_id")->constrained("formats");
            $table->text("path");
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
