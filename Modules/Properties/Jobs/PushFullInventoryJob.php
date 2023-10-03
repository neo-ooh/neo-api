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

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Neo\Modules\Properties\Jobs\Products\PushProductJob;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\Property;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\Output;

class PushFullInventoryJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected Output|null $output = null;

	public function __construct(protected int $inventoryId, protected bool $debug = false) {
		if (App::runningInConsole() && $this->debug) {
			$this->output = new ConsoleOutput();
		}
	}

	public function handle(): void {
		// We need to list all the products that require synchronization.
		// Those are products without any settings set for this inventory whose property has settings enabling them for a push on this inventory
		// plus products with settings enabling them for push on this inventory. In any case, the product `updated_at` field should be set to a datetime after the last pull of the inventory
		// We list products for both situation, dedup, and run the regular push job on them.
		$provider = InventoryProvider::query()->findOrFail($this->inventoryId);

		$this->output?->writeln("List properties and products push-enabled...");

		// Products enabled through their property
		$properties = Property::query()->whereHas("inventories_settings", function (Builder $query) {
			$query->where("inventory_id", "=", $this->inventoryId)
			      ->where("push_enabled", "=", true);
		})->orWhereDoesntHave("inventories_settings", function (Builder $query) {
			$query->where("inventory_id", "=", $this->inventoryId);
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
				          ->when($provider->last_push_at !== null, fn(Builder $query) => $query->where("updated_at", ">", $provider->last_push_at))
				          ->get()
			);
		}

		// Load products enabled individually
		$products->push(
			...Product::query()
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
			          ->when($provider->last_push_at !== null, fn(Builder $query) => $query->where("updated_at", ">", $provider->last_push_at))
			          ->get()
		);

		$products = $products->unique();
		$products->load("property");

		$this->output?->writeln("Listed {$products->count()} products");

		$progress = null;

		if ($this->output) {
			$progress = new ProgressBar($this->output->section(), $products->count());
			$progress->setFormat("%current%/%max% [%bar%] %percent:3s%% %message%");
			$progress->setMessage("");
			$progress->start();
		}

		// Now that we have listed all our products, we push them all one by one
		/** @var Product $product */
		foreach ($products as $product) {
			if ($this->output) {
				$progress?->advance();
				$progress?->setMessage($product->property->actor->name . ": " . $product->name_en);
			}

			(new PushProductJob($product->inventory_resource_id, $this->inventoryId))->handle();
		}

		if ($this->output) {
			$progress?->finish();
		}

		$provider->last_push_at = Carbon::now()->shiftTimezone("utc");
		InventoryProvider::withoutTimestampsOn($provider, fn() => $provider->save());
	}
}
