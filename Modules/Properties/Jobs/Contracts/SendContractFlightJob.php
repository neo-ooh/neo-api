<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SendContractFlightJob.php
 */

namespace Neo\Modules\Properties\Jobs\Contracts;

use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;
use Neo\Modules\Properties\Models\ExternalInventoryResource;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\MobileProduct;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\Odoo\OdooAdapter;
use Neo\Modules\Properties\Services\Resources\ContractLineResource;
use Neo\Modules\Properties\Services\Resources\ContractResource;
use Neo\Modules\Properties\Services\Resources\Enums\ContractLineType;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\CPCompiledFlight;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\Mobile\CPCompiledMobileFlight;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHCategory;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHFlight;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHProduct;
use Neo\Resources\CampaignPlannerPlan\CompiledPlan\OOH\CPCompiledOOHProperty;
use Spatie\LaravelData\Optional;

class SendContractFlightJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public int $tries = 1;
	public int $timeout = 300;

	/**
	 * @var Collection List of all Connect's products included in this flight
	 */
	protected Collection $products;

	/**
	 * @var Collection List of all Connect's properties included in this flight
	 */
	protected Collection $properties;

	public function __construct(protected ContractResource $contract, protected CPCompiledFlight $flight, protected int $flightIndex) {
	}

	/**
	 * @throws OdooException
	 * @throws JsonException
	 */
	public function handle(): array {
		$messages = [];

		$provider = InventoryProvider::find($this->contract->contract_id->inventory_id);
		/** @var OdooAdapter $odoo */
		$inventory = InventoryAdapterFactory::make($provider);

		$lines = collect();
		if ($this->flight->isOOHFlight()) {
			$lines = $this->prepareOOHLines($this->flight->getAsOOHFlight(), $this->flight, $inventory);
		} else if ($this->flight->isMobileFlight()) {
			$lines = $this->prepareMobileLines($this->flight->getAsMobileFlight(), $this->flight, $inventory);
		}

		if ($lines->count() === 0) {
			// No lines, nothing to do
			return ["Empty flight"];
		}

		// Insert the lines in the contract
		$inventory->fillContractLines(
			contract: $this->contract->contract_id,
			lines   : $lines,
		);

		// And we are done.
		return $messages;
	}

	protected function prepareOOHLines(CPCompiledOOHFlight $flight, CPCompiledFlight $baseFlight, InventoryAdapter $inventory) {
		// First, we extract all the products from the flight
		$compiledProducts = $flight->properties
			->toCollection()
			->flatMap(fn(CPCompiledOOHProperty $property) => $property->categories
				->toCollection()
				->flatMap(fn(CPCompiledOOHCategory $category) => $category->products->toCollection())
			);

		// Now we need to load the products from Connect
		$this->products         = new Collection();
		$compiledProductsChunks = $compiledProducts->chunk(500);

		// Eloquent `whereIn` fails silently for references above ~1000 reference values
		foreach ($compiledProductsChunks as $chunk) {
			$this->products = $this->products->merge(Product::query()
			                                                ->whereIn("id", $chunk->pluck("id")->toArray())
			                                                ->with("external_representations")
			                                                ->get());
		}

		// Load linked products id as well
		$linkedProductsIds       = $this->products->pluck("linked_product_id")->filter()->unique();
		$loadedProductsIds       = $this->products->pluck("id");
		$linkedProductsIds       = $linkedProductsIds->whereNotIn(null, $loadedProductsIds);
		$linkedProductsIdsChunks = $linkedProductsIds->chunk(500);

		foreach ($linkedProductsIdsChunks as $chunk) {
			$this->products = $this->products->merge(Product::query()
			                                                ->whereIn("id", $chunk)
			                                                ->get());
		}

		// This will hold the sum of production costs for each product category
		/** @var array<int, array<int, float>> $productionCosts Production cost accumulation cost
		 * First index: Category ID; Second index: Cost; Value: Count
		 */
		$productionCosts = [];

		$orderLines = collect();

		// Now, we loop over each compiled product, and build its orderLines
		/** @var CPCompiledOOHProduct $compiledProduct */
		foreach ($compiledProducts as $compiledProduct) {
			/** @var Product|null $product */
			$product = $this->products->firstWhere("id", "=", $compiledProduct->id);

			if (!$product) {
				$messages[] = "Unknown product " . $compiledProduct->id;
				continue;
			}

//			$orderLinesToAdd->push(...$this->buildLines($dbProduct, $compiledProduct, $inventory));

			/** @var ExternalInventoryResource|null $externalRepresentation */
			$externalRepresentation = $product->external_representations
				->firstWhere("inventory_id", "=", $inventory->getInventoryID());

			// Cannot send product without a representation
			if (!$externalRepresentation) {
				// TODO: provide some feedback for this situation; let the user know which products where skipped
				$messages[] = "Not representation on inventory #" . $inventory->getInventoryID() . "(" . $inventory->getInventoryType()->value . ") for product #" . $product->getKey() . " (" . $product->name_en . ")";
				return collect();
			}

			$orderLines->push(new ContractLineResource(
				                  line_id                 : null,
				                  product_id              : $externalRepresentation->toInventoryResourceId(),
				                  order                   : $this->flightIndex * 500 + $orderLines->count(),
				                  name                    : $product->name_en,
				                  start_date              : $flight->start_date,
				                  end_date                : $flight->end_date,
				                  type                    : ContractLineType::from($flight->type->value),
				                  is_linked               : false,
				                  faces_count             : $compiledProduct->quantity,
				                  spots_count             : $compiledProduct->spots,
				                  traffic                 : $compiledProduct->traffic,
				                  impressions             : $compiledProduct->impressions,
				                  unit_price              : $compiledProduct->unit_price,
				                  media_value             : $compiledProduct->media_value,
				                  discount_amount_relative: $compiledProduct->discount_amount,
				                  discount_amount         : $compiledProduct->media_value * ($compiledProduct->discount_amount / 100),
				                  price                   : $compiledProduct->price - $compiledProduct->production_cost_value,
				                  cpm                     : $compiledProduct->cpm,
			                  ));

			// If the product has a linked product, we create a line for it too
			if ($product->linked_product_id) {
				/** @var Product|null $linkedProduct */
				$linkedProduct = $this->products->firstWhere("id", "=", $product->linked_product_id);

				if (!$linkedProduct) {
					return $orderLines;
				}

				/** @var ExternalInventoryResource|null $externalRepresentation */
				$linkedProductExternalRepresentation = $linkedProduct->external_representations->firstWhere("inventory_id", "=", 1);

				// Cannot send product without a representation
				if (!$linkedProductExternalRepresentation) {
					return $orderLines;
				}

				$orderLines->push(new ContractLineResource(
					                  line_id                 : null,
					                  product_id              : $externalRepresentation->toInventoryResourceId(),
					                  order                   : $this->flightIndex * 500 + $orderLines->count(),
					                  name                    : $product->name_en,
					                  start_date              : $flight->start_date,
					                  end_date                : $flight->end_date,
					                  type                    : ContractLineType::from($flight->type->value),
					                  is_linked               : true,
					                  faces_count             : $compiledProduct->quantity,
					                  spots_count             : $compiledProduct->spots,
					                  traffic                 : 0,
					                  impressions             : 0,
					                  unit_price              : 0,
					                  media_value             : 0,
					                  discount_amount_relative: 0,
					                  discount_amount         : 0,
					                  price                   : 0,
					                  cpm                     : $compiledProduct->cpm,
				                  ));
			}

			// Register production costs
			if ($compiledProduct->production_cost_value > 0) {
				if (isset($productionCosts[$compiledProduct->category_id])) {
					if (isset($productionCosts[$compiledProduct->category_id][$compiledProduct->production_cost_value])) {
						$productionCosts[$compiledProduct->category_id][$compiledProduct->production_cost_value] += 1;
					} else {
						$productionCosts[$compiledProduct->category_id][$compiledProduct->production_cost_value] = 1;
					}
				} else {
					$productionCosts[$compiledProduct->category_id] = [$compiledProduct->production_cost_value => 1];
				}
			}
		}

		// Now the production costs
		// Load all the products categories
		$productCategories     = ProductCategory::query()->with("external_representations")
		                                        ->findMany(array_keys($productionCosts));
		$flightStartPlusOneDay = $this->flight->start_date->addDay()->toDateString();

		// Build the productions costs' lines
		foreach ($productCategories as $productCategory) {
			/** @var int|Optional $productionProductId */
			$productionProductId = $productCategory->external_representations
				->firstWhere("inventory_id", "=", $inventory->getInventoryID())?->context?->production_product_id;

			if (!$productionProductId || $productionProductId instanceof Optional) {
				clock("Missing production product Id for category #{$productCategory->getKey()}");
				$messages[] = "Missing production product Id for category #{$productCategory->getKey()} ({$productCategory->name_en})";
				continue;
			}

			// Load the product from id to get the variant id
			$productionProduct = $inventory->getProduct(new InventoryResourceId(
				                                            inventory_id: $inventory->getInventoryID(),
				                                            type        : InventoryResourceType::Product,
				                                            external_id : $productionProductId,
			                                            ));

			if (!$productionProduct) {
				// Could not find production product, stop here
				$messages[] = "Could not find Odoo product with ID #{$productionProductId}";
				continue;
			}

			foreach ($productionCosts[$productCategory->getKey()] as $amount => $quantity) {
				$orderLines->push(new ContractLineResource(
					                  line_id                 : null,
					                  product_id              : $productionProduct->resourceId,
					                  order                   : $this->flightIndex * 500 + $orderLines->count(),
					                  name                    : $productionProduct->product->name[0]->value,
					                  start_date              : $flight->start_date,
					                  end_date                : $flightStartPlusOneDay,
					                  type                    : ContractLineType::ProductionCost,
					                  is_linked               : false,
					                  faces_count             : 1,
					                  spots_count             : $quantity,
					                  traffic                 : 0,
					                  impressions             : 0,
					                  unit_price              : $amount,
					                  media_value             : 0,
					                  discount_amount_relative: 0,
					                  discount_amount         : 0,
					                  price                   : $amount,
					                  cpm                     : 0,
				                  ));
			}
		}

		return $orderLines;
	}

	public function prepareMobileLines(CPCompiledMobileFlight $flight, CPCompiledFlight $baseFlight, InventoryAdapter $inventory) {
		$product = MobileProduct::query()->find($flight->product_id);

		return collect([new ContractLineResource(
			                line_id                 : null,
			                product_id              : new InventoryResourceId(
				                                          inventory_id: $inventory->getInventoryID(),
				                                          type        : InventoryResourceType::Product,
				                                          external_id : 0,
			                                          ),
			                order                   : $this->flightIndex * 500,
			                name                    : "Audience extension",
			                start_date              : $flight->start_date,
			                end_date                : $flight->end_date,
			                type                    : ContractLineType::Mobile,
			                is_linked               : false,
			                faces_count             : 1,
			                spots_count             : 1,
			                traffic                 : 0,
			                impressions             : $flight->impressions,
			                unit_price              : $flight->media_value / $baseFlight->getWeekLength(),
			                media_value             : $flight->media_value,
			                discount_amount_relative: 0,
			                discount_amount         : 0,
			                price                   : $flight->price,
			                cpm                     : $flight->cpm,
			                description             : $flight->properties->count() . " Properties.\n" . ($flight->audience_targeting ?? ""),
			                targeting               : $flight->additional_targeting ?? "",
			                mobile_type             : $product?->name_en,
		                )]);
	}
}
