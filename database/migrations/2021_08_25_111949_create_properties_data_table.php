<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_08_25_111949_create_properties_data_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Property;
use Neo\Models\PropertyData;

class CreatePropertiesDataTable extends Migration {
    public function up() {
        Schema::create('properties_data', function (Blueprint $table) {
            $table->foreignId("property_id")
                  ->primary()
                  ->constrained("properties", "actor_id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->string("website", 128)->nullable();
            $table->text("description_fr")->nullable();
            $table->text("description_en")->nullable();
            $table->unsignedInteger("stores_count")->nullable();
            $table->unsignedInteger("visit_length")->nullable();
            $table->unsignedBigInteger("average_income")->nullable();
            $table->boolean("is_downtown")->nullable();
            $table->unsignedBigInteger("market_population")->nullable();
            $table->unsignedBigInteger("gross_area")->nullable();
            $table->unsignedBigInteger("spending_per_visit")->nullable();
            $table->string("data_source", 64)->nullable();
        });

        // Create an entry in the table for already existing properties
        $properties = Property::all();
        foreach($properties as $property) {
            $property->data()->save(new PropertyData());
        }
    }

    public function down() {
        Schema::dropIfExists('properties_data');
    }
}
