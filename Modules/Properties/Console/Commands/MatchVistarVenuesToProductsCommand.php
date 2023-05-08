<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MatchVistarVenuesToProductsCommand.php
 */

namespace Neo\Modules\Properties\Console\Commands;

use Illuminate\Console\Command;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryType;
use Neo\Modules\Properties\Services\Reach\ReachAdapter;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;

class MatchVistarVenuesToProductsCommand extends Command {
    protected $signature = 'vistar:match-venues-to-products {inventory}';

    protected $description = 'Match Vistar venues';

    /**
     * @throws InvalidInventoryAdapterException
     */
    public function handle(): void {
        $inventoryId = $this->argument("inventory");

        $provider = InventoryProvider::query()->findOrFail($inventoryId);
        /** @var ReachAdapter $inventory */
        $inventory = $provider->getAdapter();

        if ($inventory->getInventoryType() !== InventoryType::Reach) {
            $this->output->error("Bad Inventory ID");
            return;
        }

        $screens = $inventory->listProducts();

        $notMatched = [];

        /** @var IdentifiableProduct $screen */
        foreach ($screens as $screen) {

            // Ignore disabled products
            if (!$screen->product->is_sellable) {
                continue;
            }

            $this->output->write("#" . $screen->resourceId->external_id . " " . $screen->product->name[0]->value);

            // Find the location this screen is representing
            $locationExternalId = $screen->resourceId->context["location_external_id"];

            $location = Location::query()->where("external_id", "=", $locationExternalId)->first();

            if (!$location) {
                $this->output->writeln(": No location found.");
                $notMatched[] = $screen->resourceId->external_id . ":" . $screen->product->name[0]->value;
                continue;
            }

            $location->load("products.property");

            $products = $location->products->where("is_bonus", "=", false);
            if ($products->count() === 0) {
                $this->output->writeln(": No products found for screen.");
                $notMatched[] = $screen->resourceId->external_id . ":" . $screen->product->name[0]->value;
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
                if (is_array($representation->context->screens)) {
                    $representation->context->screens[$location->getKey()] = [
                        "id"   => $screen->resourceId->external_id,
                        "name" => $screen->product->name[0]->value,
                    ];
                } else {
                    $representation->context->screens = [$location->getKey() => [
                        "id"   => $screen->resourceId->external_id,
                        "name" => $screen->product->name[0]->value,
                    ]];
                }
            } else {
                $representation = new ExternalInventoryResource([
                                                                    "resource_id"  => $product->inventory_resource_id,
                                                                    "inventory_id" => $inventoryId,
                                                                    "type"         => InventoryResourceType::Product,
                                                                    "external_id"  => "MULTIPLE",
                                                                    "context"      => [
                                                                        "venue_type_id" => $screen->resourceId->context["venue_type_id"],
                                                                        "screens"       => [
                                                                            $location->getKey() => [
                                                                                "id"   => $screen->resourceId->external_id,
                                                                                "name" => $screen->product->name[0]->value,
                                                                            ],
                                                                        ],
                                                                    ],
                                                                ]);
            }

            $representation->save();

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

        foreach ($notMatched as $screenName) {
            $this->output->warning($screenName);
        }
    }
}