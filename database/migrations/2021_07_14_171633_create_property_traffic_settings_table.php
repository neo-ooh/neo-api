<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Property;

return new class extends Migration
{
    /**
     * Schema table name to migrate
     * @var string
     */
    public string $tableName = "property_traffic_settings";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->foreignId("property_id")->constrained("properties", "actor_id");
            $table->boolean("is_required")->default(false);
            $table->unsignedInteger("start_year")->default(date("Y") - 2);
            $table->timestamp("grace_override")->nullable()->default(null);
            $table->set("input_method", ["MANUAL", "LINKETT"])->default("MANUAL");
            $table->set("missing_value_strategy", ["USE_LAST", "USE_PLACEHOLDER"])->default("USE_LAST");
            $table->unsignedInteger("placeholder_value")->default(0);
            $table->timestamps();
        });

        // Migrate existing properties
        foreach (Property::all() as $property) {
            $property->traffic()->create([
                "is_required" => $property->require_traffic,
                "start_year" => $property->traffic_start_year,
                "grace_override" => $property->traffic_grace_override,
            ]);
        }
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
