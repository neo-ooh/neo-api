<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OdooAdapter.php
 */

namespace Neo\Modules\Properties\Services\Odoo;

use ArrayAccess;
use Carbon\Carbon;
use Edujugon\Laradoo\Exceptions\OdooException;
use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use JsonException;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\InventoryResourceNotFound;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\InventoryConfig;
use Neo\Modules\Properties\Services\Odoo\API\OdooClient;
use Neo\Modules\Properties\Services\Odoo\Models\Campaign;
use Neo\Modules\Properties\Services\Odoo\Models\Contract;
use Neo\Modules\Properties\Services\Odoo\Models\OrderLine;
use Neo\Modules\Properties\Services\Odoo\Models\Product;
use Neo\Modules\Properties\Services\Odoo\Models\ProductCategory;
use Neo\Modules\Properties\Services\Odoo\Models\Property;
use Neo\Modules\Properties\Services\Odoo\Models\Province;
use Neo\Modules\Properties\Services\Resources\ContractLineResource;
use Neo\Modules\Properties\Services\Resources\ContractResource;
use Neo\Modules\Properties\Services\Resources\Enums\ContractLineType;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Modules\Properties\Services\Resources\ProductCategoryResource;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use Neo\Modules\Properties\Services\Resources\PropertyResource;
use Traversable;

/**
 * @extends InventoryAdapter<OdooConfig>
 */
class OdooAdapter extends InventoryAdapter {

	protected array $capabilities = [
		InventoryCapability::ProductsRead,
		InventoryCapability::ProductsWrite,
		InventoryCapability::ProductsQuantity,
		InventoryCapability::PropertiesRead,
		InventoryCapability::PropertiesProducts,
		InventoryCapability::ProductCategoriesRead,
		InventoryCapability::ContractsRead,
		InventoryCapability::ContractsWrite,
	];

	public static function buildConfig(InventoryProvider $provider): InventoryConfig {
		return new OdooConfig(
			name         : $provider->name,
			inventoryID  : $provider->getKey(),
			inventoryUUID: $provider->uuid,
			api_url      : $provider->settings->api_url,
			api_username : $provider->settings->api_username,
			api_key      : $provider->settings->api_key,
			database     : $provider->settings->database
		);
	}

	/**
	 * @return bool|string
	 */
	public function validateConfiguration(): bool|string {
		try {
			$this->getConfig()->getClient();
			return true;
		} catch (OdooException $e) {
			return $e->getMessage();
		}
	}

	/**
	 * @param OdooClient $client
	 * @param array      $filters
	 * @return Generator
	 * @throws JsonException
	 * @throws OdooException
	 */
	protected function fetchAllProducts(OdooClient $client, array $filters): Generator {
		$pageSize = 500;
		$cursor   = 0;

		do {
			$products = Product::all(
				client : $client,
				filters: $filters,
				limit  : $pageSize,
				offset : $cursor,
			);


			foreach ($products as $product) {
				yield ResourceFactory::makeIdentifiableProduct($product, $client, $this->getConfig());
			}

			$cursor += $pageSize;
		} while ($products->count() === $pageSize);
	}

	/**
	 * When using the `$ifModifiedSince` parameter, some products may be listed twice in the return values
	 *
	 * @inheritDoc
	 * @throws OdooException
	 */
	public function listProducts(Carbon|null $ifModifiedSince = null): Traversable {
		$client = $this->getConfig()->getClient();

		return LazyCollection::make(function () use ($ifModifiedSince, $client) {
			$baseFilters = [
				["shopping_center_id", "<>", false],
				["product_type_id", "<=", 3],
			];

			if ($ifModifiedSince !== null) {
				$filters = [
					...$baseFilters,
					["write_date", ">=", $ifModifiedSince->toISOString()],
				];
			}

			yield from $this->fetchAllProducts($client, $filters ?? $baseFilters);

			if ($ifModifiedSince) {
				$filters = [
					...$baseFilters,
					["shopping_center_id.write_date", ">=", $ifModifiedSince->toISOString()],
				];

				yield from $this->fetchAllProducts($client, $filters);
			}
		});
	}

	/**
	 * @inheritDoc
	 * @throws OdooException
	 * @throws JsonException
	 */
	public function getProduct(InventoryResourceId $productId): IdentifiableProduct {
		$client = $this->getConfig()->getClient();

		return ResourceFactory::makeIdentifiableProduct(
			product: Product::get($client, (int)$productId->external_id),
			client : $client,
			config : $this->getConfig(),
		);
	}

	/**
	 * @param Property        $property
	 * @param ProductResource $productResource
	 * @return void
	 * @throws OdooException
	 */
	public function fillProperty(Property $property, ProductResource $productResource): void {
		/** @var Province $province */
		$province = Province::findBy($this->getConfig()
		                                  ->getClient(), "code", strtoupper($productResource->address->city->province_slug))[0];

		$property->name     = $productResource->property_name;
		$property->street   = $productResource->address->line_1;
		$property->street2  = $productResource->address->line_2;
		$property->zip      = $productResource->address->zipcode;
		$property->city     = $productResource->address->city->name;
		$property->state_id = $province->getKey();
//        $property->country_id = 38; // Canada
		$property->annual_traffic = (int)round($productResource->weekly_traffic * (365 / 7));
	}

	/**
	 * @param Product         $product
	 * @param ProductResource $productResource
	 * @return void
	 */
	public function fillProduct(Product $product, ProductResource $productResource): void {
		$product->product_type_id   = match ($productResource->type) {
			ProductType::Digital   => 1,
			ProductType::Static    => 2,
			ProductType::Specialty => 3,
		};
		$product->active            = $productResource->is_sellable;
		$product->categ_id          = $productResource->category_id->external_id;
		$product->bonus             = $productResource->is_bonus;
		$product->linked_product_id = $productResource->linked_product_id?->external_id;
		$product->list_price        = $productResource->price_type === PriceType::Unit ? $productResource->price : ($product->list_price ?? 0);
		$product->nb_screen         = $productResource->quantity;
		$product->nb_spots          = (int)round($productResource->loop_configuration->loop_length_ms / $productResource->loop_configuration->spot_length_ms);
	}

	/**
	 * @inheritDoc
	 * @param InventoryResourceId $productId
	 * @param ProductResource     $product
	 * @return InventoryResourceId|false
	 * @throws JsonException
	 * @throws OdooException
	 */
	public function updateProduct(InventoryResourceId $productId, ProductResource $product): InventoryResourceId|false {
		$client = $this->getConfig()->getClient();

		// Odoo supports properties, we need to update both product and property
		$odooProduct  = Product::get($client, $productId->external_id);
		$odooProperty = Property::get($client, $odooProduct->shopping_center_id[0]);

		$this->fillProperty($odooProperty, $product);
//        $odooProperty->save();

		$this->fillProduct($odooProduct, $product);
//        $odooProduct->save();

		return $productId;
	}

	/**
	 * @inheritDoc
	 * @throws OdooException
	 */
	public function listProperties(Carbon|null $ifModifiedSince = null): Traversable {
		$client = $this->getConfig()->getClient();

		return LazyCollection::make(function () use ($ifModifiedSince, $client) {
			$filters = [
			];

			if ($ifModifiedSince !== null) {
				$filters[] = ["write_date", ">=", $ifModifiedSince->toISOString()];
			}

			$pageSize = 25;
			$cursor   = 0;

			do {
				$properties = Property::all(
					client : $client,
					filters: $filters,
					fields : ["id", "name"],
					limit  : $pageSize,
					offset : $cursor,
				);

				foreach ($properties as $property) {
					yield ResourceFactory::makeIdentifiableProperty($property, $this->getConfig());
				}

				$cursor += $pageSize;
			} while ($properties->count() === $pageSize);
		});
	}

	/**
	 * @inheritDoc
	 * @param InventoryResourceId $property
	 * @return PropertyResource
	 * @throws JsonException
	 * @throws OdooException
	 */
	public function getProperty(InventoryResourceId $property): PropertyResource {
		$client = $this->getConfig()->getClient();

		return ResourceFactory::makeIdentifiableProperty(
			property: Property::get($client, (int)$property->external_id),
			config  : $this->getConfig(),
		);
	}

	/**
	 * @inheritDoc
	 * @param InventoryResourceId $property
	 * @return Traversable
	 * @throws JsonException
	 * @throws OdooException
	 */
	public function listPropertyProducts(InventoryResourceId $property): Traversable {
		$client = $this->getConfig()->getClient();

		$products = Product::all($client, filters: [["shopping_center_id", "=", (int)$property->external_id]]);

		return $products->map(fn(Product $product) => ResourceFactory::makeIdentifiableProduct($product, $client, $this->getConfig()));
	}

	/**
	 * @inheritDoc
	 * @throws OdooException
	 * @throws JsonException
	 */
	public function getProductCategory(InventoryResourceId $productCategory): ProductCategoryResource {
		$client = $this->getConfig()->getClient();

		return ResourceFactory::makeProductCategory(
			category: ProductCategory::get($client, (int)$productCategory->external_id),
			config  : $this->getConfig()
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Contracts
	|--------------------------------------------------------------------------
	*/

	/**
	 * @param Carbon|null $ifModifiedSince
	 * @return Traversable
	 * @throws OdooException
	 */
	public function listContracts(?Carbon $ifModifiedSince = null): Traversable {
		$client = $this->getConfig()->getClient();

		return LazyCollection::make(function () use ($client, $ifModifiedSince) {
			$filters = [];

			if ($ifModifiedSince !== null) {
				$filters[] = ["write_date", ">", $ifModifiedSince->toDateString()];
			}

			$pageSize = 25;
			$cursor   = 0;

			do {
				$contracts = Contract::all(
					client : $client,
					filters: $filters,
					limit  : $pageSize,
					offset : $cursor,
				);

				/** @var Contract $contract */
				foreach ($contracts as $contract) {
					yield $contract->toResource($this->getInventoryID());
				}

				$cursor += $pageSize;
			} while ($contracts->count() === $pageSize);
		});
	}

	public function findContract(string $contractId): ContractResource|null {
		$client = $this->getConfig()->getClient();

		return Contract::findByName($client, $contractId)?->toResource($this->getInventoryID());
	}

	public function getContractInformation(InventoryResourceId $contract): ContractResource {
		$contract = Contract::get($this->getConfig()->getClient(), $contract->external_id);

		if (!$contract) {
			throw new InventoryResourceNotFound($contract);
		}

		return $contract->toResource($this->getInventoryID());
	}

	public function getContract(InventoryResourceId $contract): ContractResource {
		$client   = $this->getConfig()->getClient();
		$contract = Contract::get($client, $contract->external_id);

		if (!$contract) {
			throw new InventoryResourceNotFound($contract);
		}

		// Load the lines
		$lines = LazyCollection::make(function () use ($client, $contract) {
			$chunkSize = 50;
			$offset    = 0;

			do {
				$hasMore = false;

				/** @var Collection<OrderLine> $receivedLines */
				$receivedLines = OrderLine::all(
					client : $client,
					filters: [
						         ["order_id", '=', $contract->getKey()],
						         ["is_linked_line", '!=', 1],
					         ],
					limit  : $chunkSize,
					offset : $offset);

				$offset += $receivedLines->count();

				if ($receivedLines->count() === $chunkSize) {
					$hasMore = true;
				}

				/** @var OrderLine $line */
				foreach ($receivedLines as $line) {
					yield $line->toResource($this->getInventoryID());
				}

			} while ($hasMore);
		});

		$contractResource        = $contract->toResource($this->getInventoryID());
		$contractResource->lines = $lines;

		return $contractResource;
	}

	/**
	 * @throws OdooException
	 * @throws JsonException
	 */
	public function getContractAttachedPlan(InventoryResourceId $contract): string|null {
		$contract = Contract::get($this->getConfig()->getClient(), $contract->external_id);
		return $contract->getAttachment($contract->name . ".ccp")->datas ?? null;
	}

	public function clearContract(InventoryResourceId $contract): bool {
		$client = $this->getConfig()->getClient();

		$contract = new Contract($client, [
			"id" => (int)$contract->external_id,
		]);

		// Delete lines and campaigns in contract
		$contract->clearLines();
		Campaign::delete($client, [
			["order_id", "=", $contract->getKey()],
		]);

		return true;
	}

	public function fillContractLines(InventoryResourceId $contract, ArrayAccess|array $lines): bool {
		$client = $this->getConfig()->getClient();
		// Validate contract exist
		$contract = Contract::get($client, $contract->external_id);

		if (!$contract) {
			throw new InventoryResourceNotFound($contract);
		}

		$rawCampaigns = collect();
		$rawLines     = collect();

		/** @var ContractLineResource $line */
		foreach ($lines as $line) {
			// Start by checking if there is a campaign for this line dates
			if (!$rawCampaigns->contains(fn(array $campaign) => $campaign["date_start"] === $line->start_date && $campaign["date_end"] === $line->end_date
			)) {
				$rawCampaigns[] = [
					"order_id"   => $contract->getKey(),
					"state"      => "draft",
					"date_start" => $line->start_date,
					"date_end"   => $line->end_date,
				];
			}

			// Format the line
			$rawLines[] = [
				"order_id"           => $contract->getKey(),
				"name"               => $line->name,
				"price_unit"         => $line->unit_price,
				"product_uom_qty"    => $line->spots_count,
				"customer_lead"      => 0.0,
				"nb_screen"          => $line->faces_count,
				"product_id"         => $line->type === ContractLineType::Mobile ? 38832 : $line->product_id->context["variant_id"],
				"rental_start"       => $line->start_date,
				"rental_end"         => $line->end_date,
				"is_rental_line"     => 1,
				"is_linked_line"     => $line->is_linked,
				"discount"           => -$line->discount_amount_relative,
				"sequence"           => $line->order,
				"impression"         => $line->impressions,
				"connect_impression" => $line->impressions,
				"market_name"        => $line->description,
				"segment"            => $line->targeting,
				"impression_format"  => $line->mobile_type,
				"cpm"                => $line->cpm,
			];
		}

		// Insert the campaigns
		Campaign::createMany($client, $rawCampaigns->toArray());

		// Now, we insert the lines by batches
		$linesBatch = $rawLines->chunk(100);
		// Creates all the lines
		foreach ($linesBatch as $batch) {
			OrderLine::createMany($client, $batch->toArray());
		}

		return true;
	}

	/**
	 * @throws OdooException
	 * @throws JsonException
	 */
	public function setContractAttachedPlan(InventoryResourceId $contract, string $plan): bool {
		$contract = Contract::get($this->getConfig()->getClient(), $contract->external_id);

		$contract->removeAttachment($contract->name . ".ccp");
		$contract->storeAttachment($contract->name . ".ccp", base64_encode(gzencode($plan)));

		return true;
	}
}
