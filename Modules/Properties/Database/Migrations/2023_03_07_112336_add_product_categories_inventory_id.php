<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_07_112336_add_product_categories_inventory_id.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('products_categories', function (Blueprint $table) {
            $table->foreignId("inventory_resource_id")
                  ->after("external_id")
                  ->nullable()
                  ->constrained("inventory_resources", "id")
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->removeColumn("type_id");
            $table->renameColumn("fill_strategy", "type");
        });
    }
};
