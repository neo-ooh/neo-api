<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryAdapter.php
 */

namespace Neo\Modules\Properties\Services;

use ArrayAccess;
use Carbon\Carbon;
use Illuminate\Cache\TaggedCache;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\InventoryMethodNotSupportedException;
use Neo\Modules\Properties\Services\Exceptions\InventoryResourceNotFound;
use Neo\Modules\Properties\Services\Resources\ContractResource;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Modules\Properties\Services\Resources\ProductCategoryResource;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use Neo\Modules\Properties\Services\Resources\PropertyResource;
use Traversable;

/**
 * @template TConfig of InventoryConfig
 */
abstract class InventoryAdapter {
	/**
	 * @var array<InventoryCapability>
	 */
	protected array $capabilities;

	/**
	 * @param TConfig $config
	 */
	public function __construct(protected InventoryConfig $config, protected InventoryAdapterDelegate $delegate) {
	}

	/**
	 * @param InventoryProvider $provider
	 * @return TConfig
	 */
	abstract public static function buildConfig(InventoryProvider $provider): InventoryConfig;

	public function getInventoryType(): InventoryType {
		return $this->config->type;
	}

	public function getInventoryID(): int {
		return $this->config->inventoryID;
	}

	public function getInventoryUUID(): string {
		return $this->config->inventoryUUID;
	}

	/**
	 * @return TConfig
	 */
	public function getConfig(): InventoryConfig {
		return $this->config;
	}

	/**
	 * Tell if the broadcaster has the specified capability
	 *
	 * @param InventoryCapability $capability
	 * @return bool
	 */
	public function hasCapability(InventoryCapability $capability): bool {
		return in_array($capability, $this->capabilities, true);
	}

	/**
	 * List all the capabilities of the inventory
	 *
	 * @return InventoryCapability[]
	 */
	public function getCapabilities() {
		return $this->capabilities;
	}

	/**
	 * @return TaggedCache Get a cache instance for this inventory to cache stuff
	 */
	public function getCache(): TaggedCache {
		return $this->delegate->getCache();
	}

	/*
	|--------------------------------------------------------------------------
	| Authentication
	|--------------------------------------------------------------------------
	*/

	/**
	 * Validate inventory configuration, including authentication
	 *
	 * @return true|string
	 */
	abstract public function validateConfiguration(): bool|string;

	/*
	|--------------------------------------------------------------------------
	| Products - Read
	|--------------------------------------------------------------------------
	*/

	/**
	 * List all products available in this inventory
	 *
	 * @param Carbon|null $ifModifiedSince Limit returned products to ones that have been changed since the given date
	 * @return Traversable<IdentifiableProduct>
	 */
	public function listProducts(Carbon|null $ifModifiedSince = null): Traversable {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "listProducts");
	}

	/**
	 * Get the details of a product in this inventory
	 *
	 * @param InventoryResourceId $productId
	 * @return IdentifiableProduct
	 * @throws InventoryResourceNotFound
	 */
	public function getProduct(InventoryResourceId $productId): IdentifiableProduct {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "getProduct");
	}


	/*
	|--------------------------------------------------------------------------
	| Products - Write
	|--------------------------------------------------------------------------
	*/

	/**
	 * Creates a product with the given values in the inventory system.
	 *
	 * @param ProductResource $product
	 * @param array           $context
	 * @return InventoryResourceId|null
	 */
	public function createProduct(ProductResource $product, array $context): InventoryResourceId|null {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "createProduct");
	}

	/**
	 * Updated the specified product with the given resource
	 *
	 * @param InventoryResourceId $productId
	 * @param ProductResource     $product
	 * @return InventoryResourceId|false
	 */
	public function updateProduct(InventoryResourceId $productId, ProductResource $product): InventoryResourceId|false {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "updateProduct");
	}

	/**
	 * Remove the specified product from the inventory system
	 *
	 * @param InventoryResourceId $productId
	 * @return bool
	 */
	public function removeProduct(InventoryResourceId $productId): bool {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "removeProduct");
	}



	/*
	|--------------------------------------------------------------------------
	| Properties - Read
	|--------------------------------------------------------------------------
	*/

	/**
	 * List all properties available in the inventory
	 *
	 * @param Carbon|null $ifModifiedSince Limit returned results to properties having been updated after the given date
	 * @return Traversable<PropertyResource>
	 */
	public function listProperties(Carbon|null $ifModifiedSince = null): Traversable {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "listProperties");
	}

	/**
	 * Get a specific property from the inventory
	 *
	 * @param InventoryResourceId $property
	 * @return PropertyResource
	 * @throws InventoryResourceNotFound
	 */
	public function getProperty(InventoryResourceId $property): PropertyResource {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "getProperty");
	}

	/**
	 * List the products of a specific property
	 *
	 * @param InventoryResourceId $property
	 * @return Traversable<IdentifiableProduct>
	 * @throws InventoryResourceNotFound
	 */
	public function listPropertyProducts(InventoryResourceId $property): Traversable {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "getProperty");
	}

	/*
	|--------------------------------------------------------------------------
	| Product Categories - Read
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get a specific product category from the inventory
	 *
	 * @param InventoryResourceId $productCategory
	 * @return ProductCategoryResource
	 * @throws InventoryResourceNotFound
	 */
	public function getProductCategory(InventoryResourceId $productCategory): ProductCategoryResource {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "getProductCategory");
	}

	/*
	|--------------------------------------------------------------------------
	| Contracts - Read
	|--------------------------------------------------------------------------
	*/

	/**
	 * Search for a contract on the inventory using its ID. Returns a contract information without its lines
	 *
	 * @param string $contractId
	 * @return ContractResource|null
	 */
	public function findContract(string $contractId): ContractResource|null {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "findContract");
	}

	/**
	 * Gets a contract information without its lines
	 *
	 * @param InventoryResourceId $contract
	 * @return ContractResource
	 */
	public function getContractInformation(InventoryResourceId $contract): ContractResource {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "getContract");
	}

	/**
	 * Gets a contract information and all its lines
	 *
	 * @param InventoryResourceId $contract
	 * @return ContractResource
	 */
	public function getContract(InventoryResourceId $contract): ContractResource {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "getContract");
	}

	public function getContractAttachedPlan(InventoryResourceId $contract): string|null {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "getContractAttachedPlan");
	}

	/*
	|--------------------------------------------------------------------------
	| Contracts - Write
	|--------------------------------------------------------------------------
	*/

	public function clearContract(InventoryResourceId $contract): bool {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "clearContract");
	}

	public function fillContractLines(InventoryResourceId $contract, array|ArrayAccess $lines): bool {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "fillContractLines");
	}

	public function setContractAttachedPlan(InventoryResourceId $contract, string $plan): bool {
		throw new InventoryMethodNotSupportedException($this->getInventoryID(), $this->getInventoryType(), "setContractAttachedPlan");
	}
}
