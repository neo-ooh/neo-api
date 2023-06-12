<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullFullInventoryJob.php
 */

namespace Neo\Modules\Properties\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Neo\Modules\Properties\Jobs\Products\ImportProductJob;
use Neo\Modules\Properties\Jobs\Products\PullProductJob;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;

class PullFullInventoryJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Output|null $output = null;

    public function __construct(protected int $inventoryId, protected bool $debug = false) {
        if (App::runningInConsole() && $this->debug) {
            $this->output = new ConsoleOutput();
        }
    }

    /**
     * @throws InvalidInventoryAdapterException
     */
    public function handle(): void {
        // We need to list all the products that require synchronization.
        // Those are products without any settings set for this inventory whose property has settings enabling them for a pull on this inventory
        // plus products with settings enabling them for pull on this inventory.
        // We list products for both situtation, dedup, and run the regular pull job on them.

        $provider  = InventoryProvider::query()->findOrFail($this->inventoryId);
        $inventory = $provider->getAdapter();

        $this->output?->writeln("List properties and products pull-enabled...");

        // List properties with pull enabled for the current inventory
        /** @var Collection<Property> $properties */
        $properties = Property::query()->whereHas("inventories_settings", function (Builder $query) {
            $query->where("inventory_id", "=", $this->inventoryId)
                  ->where("pull_enabled", "=", true);
        })->whereHas("external_representations", function (Builder $query) {
            $query->where("inventory_id", "=", $this->inventoryId)
                  ->withoutTrashed();
        })->get();
        $properties->load("external_representations");

        /** @var Collection<Product> $products */
        $products = new Collection();

        foreach ($properties->chunk(500) as $propertiesChunk) {
            $products->push(
                ...Product::query()
                          ->whereIn("property_id", $propertiesChunk->pluck("actor_id"))
                          ->where(function (Builder $query) {
                              $query->whereHas("inventories_settings", function (Builder $query) {
                                  $query->where("inventory_id", "=", $this->inventoryId)
                                        ->where("pull_enabled", "=", true);
                              })->orWhereDoesntHave("inventories_settings", function (Builder $query) {
                                  $query->where("inventory_id", "=", $this->inventoryId);
                              });
                          })
                          ->whereHas("external_representations", function (Builder $query) {
                              $query->where("inventory_id", "=", $this->inventoryId)
                                    ->withoutTrashed();
                          })
//                          ->where("updated_at", ">", Carbon::now()->subDays(3))
                          ->get()
            );
        }

        // Load products enabled individually
        $products->push(
            ...Product::query()
                      ->whereHas("inventories_settings", function (Builder $query) {
                          $query->where("inventory_id", "=", $this->inventoryId)
                                ->where("pull_enabled", "=", true);
                      })
                      ->whereHas("external_representations", function (Builder $query) {
                          $query->where("inventory_id", "=", $this->inventoryId)
                                ->withoutTrashed();
                      })
//                      ->where("updated_at", ">", Carbon::now()->subDays(3))
                      ->get()
        );

        $products = $products->unique();
        $products->load("external_representations");

        $this->output?->writeln("Listed {$properties->count()} properties");
        $this->output?->writeln("Listed {$products->count()} products");

        // We now have the list of all properties and products with pull enabled for this inventory.
        // List all products on the inventory that have been updated recently
        $this->output?->writeln("Listing recently updated products on inventory...");

        $externalProducts = collect($inventory->listProducts($provider->last_pull_at?->subDay()));

        $this->output?->writeln("Listed {$externalProducts->count()} recently updated");

        $progress       = null;
        $detailsSection = null;
        if (App::runningInConsole() && $this->debug) {
            $progress = new ProgressBar($this->output->section(), $externalProducts->count());
            $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
            $progress->setMessage("");
            $progress->start();

            $detailsSection = $this->output->section();
        }

        /** @var IdentifiableProduct $externalProduct */
        foreach ($externalProducts as $externalProduct) {
            if (App::runningInConsole() && $this->debug) {
                $detailsSection?->clear();
                $progress?->advance();
                $progress?->setMessage($externalProduct->product->property_name . " - " . $externalProduct->product->name[0]->value);
            }

            // Do we already have a product with the same external ID ?
            /** @var Product|null $product */
            $product = $products->first(function (Product $product) use ($externalProduct) {
                return $product->external_representations->contains(
                    function (ExternalInventoryResource $representation) use ($externalProduct) {
                        return $representation->inventory_id === $this->inventoryId
                            && $representation->external_id === $externalProduct->resourceId->external_id;
                    });
            });

            if ($product !== null) {
                $detailsSection?->write("Updating existing product...");
                // We do, trigger a regular pull for this product
                (new PullProductJob($product->inventory_resource_id, $this->inventoryId, $externalProduct))->handle();
                continue;
            }

            // This external product does not match any existing product. Check if it matches an existing property
            /** @var Property|null $property */
            $property = $properties->first(function (Property $property) use ($externalProduct) {
                return $property->external_representations->contains(
                    function (ExternalInventoryResource $representation) use ($externalProduct) {
                        return $representation->inventory_id === $this->inventoryId
                            && $representation->external_id === $externalProduct->product->property_id->external_id;
                    });
            });

            if ($property !== null && $property->inventories_settings->firstWhere("inventory_id", "=", $this->inventoryId)?->auto_import_products) {
                $detailsSection?->write("Importing new product...");
                // There is a property already associated with the external products' property
                (new ImportProductJob($this->inventoryId, $property->getKey(), $externalProduct->resourceId, $externalProduct))->handle();
            }
        }

        $progress?->finish();

        $provider->last_pull_at = $provider->freshTimestamp()->shiftTimezone("utc");
        $provider->save();
    }
}
