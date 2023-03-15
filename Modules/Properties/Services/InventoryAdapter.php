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

use Carbon\Carbon;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\InventoryMethodNotSupportedException;
use Neo\Modules\Properties\Services\Exceptions\InventoryResourceNotFound;
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
    public function __construct(protected InventoryConfig $config) {
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

    /*
    |--------------------------------------------------------------------------
    | Products - Read
    |--------------------------------------------------------------------------
    */

    /**
     * List all products available in this inventory
     *
     * @param Carbon|null $ifModifiedSince Limit returned products to ones that have been changed since the given date
     * @return Traversable
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
     * @return bool
     */
    public function updateProduct(InventoryResourceId $productId, ProductResource $product): bool {
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
     * @return Traversable
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
}
