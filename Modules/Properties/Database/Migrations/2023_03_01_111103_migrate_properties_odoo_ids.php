<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_01_111103_migrate_properties_odoo_ids.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Odoo\Property;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up() {
        // We want to move all the properties odoo ids from their dedicated table to
        // the `external_inventories_resources` row

        // Get the sole provider, which is the odoo one, inserted in the previous migration file
        /** @var InventoryProvider $provider */
        $provider = InventoryProvider::query()->first();

        // Load all properties
        $properties = DB::table("properties")
                        ->orderBy('actor_id')
                        ->lazy();

        /** @var Collection<Property> $odooProperties */
        $odooProperties = DB::table("odoo_properties")
                            ->get();

        $output = new ConsoleOutput();
        $output->writeln("");
        $progress = new ProgressBar($output);
        $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
        $progress->start($properties->count());

        /** @var Product $property */
        foreach ($properties as $property) {
            $progress->setMessage("Property #$property->actor_id");
            $progress->advance();

            // Get the property Odoo ID
            /** @var Property|null $odooID */
            $odooID = $odooProperties->firstWhere("property_id", "===", $property->actor_id);

            if (!$odooID) {
                // Nothing to migrate
                continue;
            }

            // Insert external resource
            DB::table("external_inventories_resources")
              ->insert([
                           "resource_id"  => $property->inventory_resource_id,
                           "inventory_id" => $provider->getKey(),
                           "type"         => InventoryResourceType::Property,
                           "external_id"  => $odooID->odoo_id,
                           "context"      => json_encode([]),
                       ]);

            // Add the link for the properties
            DB::table("resource_inventories_settings")
              ->insert([
                           "resource_id"          => $property->inventory_resource_id,
                           "inventory_id"         => $provider->getKey(),
                           "is_enabled"           => true,
                           "pull_enabled"         => true,
                           "push_enabled"         => false,
                           "auto_import_products" => true,
                           "settings"             => json_encode([]),
                       ]);
        }

        $progress->finish();
        $output->writeln("");
    }
};
