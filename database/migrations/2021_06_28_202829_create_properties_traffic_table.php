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
    public string $tableName = "properties_traffic";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->foreignId("property_id")->constrained("properties", "actor_id");
            $table->year("year");
            $table->unsignedTinyInteger("month");
            $table->unsignedBigInteger("traffic");
            $table->timestamps();
        });

        Schema::table($this->tableName, function (Blueprint $table) {
            $table->primary(["property_id", "year", "month"]);
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
