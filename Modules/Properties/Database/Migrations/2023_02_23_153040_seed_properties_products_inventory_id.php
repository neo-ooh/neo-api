<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_23_153040_seed_properties_products_inventory_id.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Properties\Models\InventoryResource;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;

return new class extends Migration {
    public function up() {
        // For each property and product, create an `inventory_resource` id and assign it.
        $properties = DB::table("properties")->get("actor_id");

        foreach ($properties as $property) {
            $inventoryResource = InventoryResource::query()->create(["type" => InventoryResourceType::Property]);

            DB::table("properties")->where("actor_id", "=", $property->actor_id)
              ->update(["inventory_resource_id" => $inventoryResource->getKey()]);
        }


        $products = DB::table("products")->get("id");

        foreach ($products as $product) {
            $inventoryResource = InventoryResource::query()->create(["type" => InventoryResourceType::Product]);

            DB::table("products")->where("id", "=", $product->id)
              ->update(["inventory_resource_id" => $inventoryResource->getKey()]);
        }
    }
};
