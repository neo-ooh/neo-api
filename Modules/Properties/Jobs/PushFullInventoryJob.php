<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PushFullInventoryJob.php
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
use Neo\Modules\Properties\Jobs\Products\PushProductJob;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\Property;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class PushFullInventoryJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $inventoryId) {
    }

    public function handle(): void {
        // We need to list all the products that require synchronization.
        // Those are products without any settings set for this inventory whose property has settings enabling them for a push on this inventory
        // plus products with settings enabling them for push on this inventory.
        // We list products for both situation, dedup, and run the regular push job on them.

        // Products enabled through their property
        $properties = Property::query()->whereHas("inventories_settings", function (Builder $query) {
            $query->where("inventory_id", "=", $this->inventoryId)
                  ->where("push_enabled", "=", true);
        })->whereHas("external_representations", function (Builder $query) {
            $query->where("inventory_id", "=", $this->inventoryId)
                  ->withoutTrashed();
        })->get();

        $products = new Collection();

        foreach ($properties->chunk(500) as $propertiesChunk) {
            $products->push(
                ...Product::query()
                          ->whereIn("property_id", $propertiesChunk->pluck("actor_id"))
                          ->where(function (Builder $query) {
                              $query->whereHas("inventories_settings", function (Builder $query) {
                                  $query->where("inventory_id", "=", $this->inventoryId)
                                        ->where("push_enabled", "=", true);
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
                                ->where("push_enabled", "=", true);
                      })
                      ->whereHas("external_representations", function (Builder $query) {
                          $query->where("inventory_id", "=", $this->inventoryId)
                                ->withoutTrashed();
                      })
//                      ->where("updated_at", ">", Carbon::now()->subDays(3))
                      ->get()
        );

        $products = $products->unique();

        $products->load("property");

        if (App::runningInConsole()) {
            $output   = new ConsoleOutput();
            $progress = new ProgressBar($output, $products->count());
            $progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
            $progress->setMessage("");
            $progress->start();
        }

        // Now that we have listed all our products, we push them all one by one
        /** @var Product $product */
        foreach ($products as $product) {
            if (App::runningInConsole()) {
                $progress?->advance();
                $progress?->setMessage($product->property->actor->name . ": " . $product->name_en);
            }

            (new PushProductJob($product->inventory_resource_id, $this->inventoryId))->handle();
        }

        $progress?->finish();
    }
}
