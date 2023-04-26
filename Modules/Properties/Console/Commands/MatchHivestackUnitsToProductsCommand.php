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
    protected $signature = 'hivestack:match-units-to-products';

    protected $description = 'Command description';

    protected int $inventoryID = 4;

    public function handle(): void {
        $provider = InventoryProvider::query()->find($this->inventoryID);
        /** @var InventoryAdapter $inventory */
        $inventory = $provider->getAdapter();

        $products = $inventory->listProducts();
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
                continue;
            }

            $location->load("products.property");

            $products = $location->products->where("is_bonus", "=", false);
            if ($products->count() === 0) {
                $this->output->writeln(": No products found for location.");
                continue;
            }

            if ($products->count() > 1) {
                $this->output->write(" (Multiple products found!)");
            }

            /** @var Product $product */
            $product = $products->first();

            // Does this product already has an external representation for this inventory ?
            /** @var ExternalInventoryResource $representation */
            $representation = $product->external_representations()->firstWhere("inventory_id", "=", $this->inventoryID);

            if ($representation) {
                // Representation already exist, append current ID
                if (is_array($representation->context->units)) {
                    $representation->context->units[] = $unit->resourceId->external_id;
                    $representation->context->units   = array_unique($representation->context->units);
                }
            } else {
                $representation = new ExternalInventoryResource([
                                                                    "resource_id"  => $product->inventory_resource_id,
                                                                    "inventory_id" => $this->inventoryID,
                                                                    "type"         => InventoryResourceType::Product,
                                                                    "external_id"  => "MULTIPLE",
                                                                    "context"      => [
                                                                        "network_id"    => $unit->resourceId->context["network_id"],
                                                                        "media_type_id" => $unit->resourceId->context["media_type_id"],
                                                                        "units"         => [
                                                                            $unit->resourceId->external_id,
                                                                        ],
                                                                    ],
                                                                ]);
            }

            $representation->save();

            // We also want to validate that the property has a representation as well
            $propertyRepresentation = $product->property->external_representations()
                                                        ->firstWhere("inventory_id", "=", $this->inventoryID);

            if (!$propertyRepresentation) {
                // Add it
                $propertyRepresentation              = ExternalInventoryResource::fromInventoryResource($unit->product->property_id);
                $propertyRepresentation->resource_id = $product->property->inventory_resource_id;
                $propertyRepresentation->save();
            }

            $this->output->writeln(": Associated to " . $product->property->actor->name . " - " . $product->name_en);
        }
    }
}
