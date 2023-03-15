<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_27_145727_create_external_inventory_resources_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('external_inventories_resources', function (Blueprint $table) {
            $table->id();

            $table->foreignId("resource_id")->constrained("inventory_resources", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->foreignId("inventory_id")->constrained("inventory_providers", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();

            $table->string("type", 16);

            $table->text("external_id");
            $table->json("context");

            $table->timestamp("created_at");
            $table->foreignId("created_by");
            $table->timestamp("updated_at");
            $table->foreignId("updated_by");
            $table->timestamp("deleted_at")->nullable(true)->default(null);
            $table->foreignId("deleted_by")->nullable(true)->default(null);
        });
    }
};
