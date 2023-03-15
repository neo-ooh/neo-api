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

use Carbon\Carbon;
use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Support\LazyCollection;
use JsonException;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\InventoryConfig;
use Neo\Modules\Properties\Services\Odoo\API\OdooClient;
use Neo\Modules\Properties\Services\Odoo\Models\Product;
use Neo\Modules\Properties\Services\Odoo\Models\ProductCategory;
use Neo\Modules\Properties\Services\Odoo\Models\Property;
use Neo\Modules\Properties\Services\Odoo\Models\Province;
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

    protected function __listAllProducts(OdooClient $client, array $filters) {
        $pageSize = 25;
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
     * When using the `$ifModifiedSince` parameter, some proudcts may be listed twice in the return values
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

            yield from $this->__listAllProducts($client, $filters ?? $baseFilters);

            if ($ifModifiedSince) {
                $filters = [
                    ...$baseFilters,
                    ["shopping_center_id.write_date", ">=", $ifModifiedSince->toISOString()],
                ];

                yield from $this->__listAllProducts($client, $filters);
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

    public function fillProperty(Property $property, ProductResource $productResource) {
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
        $property->annual_traffic = $productResource->weekly_traffic * (365 / 7);
    }

    public function fillProduct(Product $product, ProductResource $productResource) {
        $product->product_type_id   = match ($productResource->type) {
            ProductType::Digital   => 1,
            ProductType::Static    => 2,
            ProductType::Specialty => 3,
        };
        $product->categ_id          = $productResource->category_id->external_id;
        $product->bonus             = $productResource->is_bonus;
        $product->linked_product_id = $productResource->linked_product_id?->external_id;
        $product->list_price        = $productResource->price_type === PriceType::Unit ? $productResource->price : ($product->list_price ?? 0);
        $product->nb_screen         = $productResource->quantity;
        $product->nb_spots          = round($productResource->loop_configuration->loop_length_ms / $productResource->loop_configuration->spot_length_ms);
    }

    /**
     * @inheritDoc
     */
    public function updateProduct(InventoryResourceId $productId, ProductResource $product): bool {
        $client = $this->getConfig()->getClient();

        // Odoo supports properties, we need to update both product and property
        $odooProduct  = Product::get($client, $productId->external_id);
        $odooProperty = Property::get($client, $odooProduct->shopping_center_id[0]);

        $this->fillProperty($odooProperty, $product);
//        $odooProperty->save();

        $this->fillProduct($odooProduct, $product);
//        $odooProduct->save();
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
}
