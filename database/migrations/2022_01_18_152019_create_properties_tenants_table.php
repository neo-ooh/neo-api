<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_01_18_152019_create_properties_tenants_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertiesTenantsTable extends Migration {
    public function up() {
        Schema::create('properties_tenants', function (Blueprint $table) {
            $table->foreignId("property_id")->constrained("properties", "actor_id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("brand_id")->constrained("brands", "id")->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::table("properties_tenants", function (Blueprint $table) {
            $table->unique(["property_id", "brand_id"]);
        });
    }

    public function down() {
        Schema::dropIfExists('properties_tenants');
    }
}
