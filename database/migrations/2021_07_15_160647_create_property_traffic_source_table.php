<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_07_15_160647_create_property_traffic_source_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Schema table name to migrate
     *
     * @var string
     */
    public string $tableName = "property_traffic_source";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->foreignId("property_id")
                  ->constrained("property_traffic_settings", "property_id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId("source_id")->constrained("traffic_sources")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string("uid");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists($this->tableName);
    }
};
