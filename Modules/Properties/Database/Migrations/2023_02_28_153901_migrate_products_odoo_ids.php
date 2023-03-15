<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_02_28_153901_migrate_products_odoo_ids.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up() {
        // We want to move products ids from columns on the products table to
        // `external_inventories_resources` rows.

        // Get the sole provider, which is the odoo one, inserted in the previous migration file
        /** @var InventoryProvider $provider */
        $provider = InventoryProvider::query()->first();

        // Load all products
        $products = DB::table("products")->orderBy('id')->lazy();

        $output = new ConsoleOutput();
        $output->writeln("");
        $progress = new ProgressBar($output);
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->start($products->count());

        /** @var Product $product */
        foreach ($products as $product) {
            $progress->setMessage("Product #$product->id");
            $progress->advance();

            if ($product->external_id === null) {
                // Nothing to migrate
                continue;
            }

            // Insert external resource
            DB::table("external_inventories_resources")
              ->insert(["resource_id"  => $product->inventory_resource_id,
                        "inventory_id" => $provider->getKey(),
                        "type"         => InventoryResourceType::Product,
                        "external_id"  => $product->external_id,
                        "context"      => json_encode([
                                                          "variant_id" => $product->external_variant_id,
                                                      ]),
                       ]);
        }

        $progress->finish();
        $output->writeln("");
    }
};
