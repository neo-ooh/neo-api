<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_27_145313_create_inventory_resources_settings_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('resource_inventories_settings', function (Blueprint $table) {
            $table->foreignId("resource_id")->constrained("inventory_resources", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("inventory_id")->constrained("inventory_providers", "id")->cascadeOnUpdate()->cascadeOnDelete();

            $table->boolean("is_enabled")->default(true);
            $table->boolean("pull_enabled");
            $table->boolean("push_enabled");
            $table->boolean("auto_import_products");
            $table->json("settings");

            $table->timestamp("created_at");
            $table->foreignId("created_by");
            $table->timestamp("updated_at");
            $table->foreignId("updated_by");

            $table->primary(["resource_id", "inventory_id"]);
        });
    }
};
