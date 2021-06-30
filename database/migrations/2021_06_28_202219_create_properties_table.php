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
    public string $tableName = "properties";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->foreignId("actor_id")->constrained("actors");
            $table->boolean("require_traffic");
            $table->unsignedInteger("traffic_start_year")->default(date("Y") - 2);
            $table->timestamp("traffic_grace_override")->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table($this->tableName, function (Blueprint $table) {
            $table->primary("actor_id");
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
