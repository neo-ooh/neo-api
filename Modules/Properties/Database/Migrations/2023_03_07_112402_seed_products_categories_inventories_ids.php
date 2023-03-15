<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_07_112402_seed_products_categories_inventories_ids.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\InventoryResource;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;

return new class extends Migration {
    public function up() {
        $categories = DB::table("products_categories")->get();

        // Get the sole provider, which is the odoo one, inserted in the previous migration file
        /** @var InventoryProvider $provider */
        $provider = InventoryProvider::query()->first();

        foreach ($categories as $category) {
            $inventoryResource = InventoryResource::query()->create(["type" => InventoryResourceType::ProductCategory]);

            DB::table("products_categories")->where("id", "=", $category->id)
              ->update(["inventory_resource_id" => $inventoryResource->getKey()]);

            // Also, migrate odoo ids for products categories
            // Insert external resource
            DB::table("external_inventories_resources")
              ->insert(["resource_id"  => $inventoryResource->getKey(),
                        "inventory_id" => $provider->getKey(),
                        "type"         => InventoryResourceType::ProductCategory,
                        "external_id"  => $category->external_id,
                        "context"      => json_encode([]),
                       ]);
        }
    }
};
