<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_05_30_144043_create_property_types_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('property_types', function (Blueprint $table) {
            $table->foreignId("id")->constrained("inventory_resources", "id");
            $table->string('name_en');
            $table->string('name_fr');
            
            $table->timestamp("created_at");
            $table->foreignId("created_by");
            $table->timestamp("updated_at");
            $table->foreignId("updated_by");
            $table->timestamp("deleted_at")->nullable(true)->default(null);
            $table->foreignId("deleted_by")->nullable(true)->default(null);
        });
    }
};
