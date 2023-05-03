<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MatchHivestackUnitsToProductsCommand.php
 */

namespace Neo\Modules\Properties\Console\Commands;

use Illuminate\Console\Command;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;

class MatchHivestackUnitsToProductsCommand extends Command {
    protected $signature = 'hivestack:match-units-to-products {inventory}';


    public function handle(): void {
        $inventoryId = $this->argument("inventory");

        $provider = InventoryProvider::query()->find($inventoryId);
        /** @var InventoryAdapter $inventory */
        $inventory = $provider->getAdapter();

        $products = $inventory->listProducts();

        $notMatched = [];

        /** @var IdentifiableProduct $unit */
        foreach ($products as $unit) {
            // Ignore disabled products
            if (!$unit->product->is_sellable) {
                continue;
            }

            $this->output->write("#" . $unit->resourceId->external_id . " " . $unit->product->name[0]->value);

            $location = Location::query()->where("external_id", "=", trim($unit->resourceId->context["external_id"]))->first();

            if (!$location) {
                $this->output->writeln(": No location found.");
                $notMatched[] = $unit->resourceId->external_id . ":" . $unit->product->name[0]->value;
                continue;
            }

            $location->load("products.property");

            $products = $location->products->where("is_bonus", "=", false);
            if ($products->count() === 0) {
                $this->output->writeln(": No products found for location.");
                $notMatched[] = $unit->resourceId->external_id . ":" . $unit->product->name[0]->value;
                continue;
            }

            if ($products->count() > 1) {
                $this->output->write(" (Multiple products found!)");
            }

            /** @var Product $product */
            $product = $products->first();

            // Does this product already has an external representation for this inventory ?
            /** @var ExternalInventoryResource $representation */
            $representation = $product->external_representations()->firstWhere("inventory_id", "=", $inventoryId);

            if ($representation) {
                // Representation already exist, append current ID
                if (is_array($representation->context->units)) {
                    $representation->context->units[$location->getKey()] = $unit->resourceId->external_id;
                }
            } else {
                $representation = new ExternalInventoryResource([
                                                                    "resource_id"  => $product->inventory_resource_id,
                                                                    "inventory_id" => $inventoryId,
                                                                    "type"         => InventoryResourceType::Product,
                                                                    "external_id"  => "MULTIPLE",
                                                                    "context"      => [
                                                                        "network_id"    => $unit->resourceId->context["network_id"],
                                                                        "media_type_id" => $unit->resourceId->context["media_type_id"],
                                                                        "units"         => [
                                                                            $location->getKey() => $unit->resourceId->external_id,
                                                                        ],
                                                                    ],
                                                                ]);
            }

            $representation->save();

            // We also want to validate that the property has a representation as well
            $propertyRepresentation = $product->property->external_representations()
                                                        ->firstWhere("inventory_id", "=", $inventoryId);

            if (!$propertyRepresentation) {
                // Add it
                $propertyRepresentation              = ExternalInventoryResource::fromInventoryResource($unit->product->property_id);
                $propertyRepresentation->resource_id = $product->property->inventory_resource_id;
                $propertyRepresentation->save();
            }

            // Set up inventory for auto push
            $inventorySettings               = $product->property->inventories_settings()
                                                                 ->where("inventory_id", "=", $inventoryId)
                                                                 ->firstOrCreate([
                                                                                     "resource_id" => $product->property->inventory_resource_id,
                                                                                                                                                                                                                                                                                                     "inventory_id" => $inventoryId,
                                                                                 ], [
                                                                                     "is_enabled"   => true,
                                                                                     "push_enabled" => true,
                                                                                     "pull_enabled" => false,
                                                                                     "settings"     => "{}",
                                                                                 ]);
            $inventorySettings->push_enabled = true;
            $inventorySettings->save();

            $this->output->writeln(": Associated to " . $product->property->actor->name . " - " . $product->name_en);
        }

        foreach ($notMatched as $unitName) {
            $this->output->warning($unitName);
        }
    }
}