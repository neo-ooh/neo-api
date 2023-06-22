<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceFactory.php
 */

namespace Neo\Modules\Properties\Services\Odoo;

use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Services\Odoo\API\OdooClient;
use Neo\Modules\Properties\Services\Odoo\Models\Product;
use Neo\Modules\Properties\Services\Odoo\Models\ProductCategory;
use Neo\Modules\Properties\Services\Odoo\Models\Property;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\Geolocation;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Modules\Properties\Services\Resources\LocalizedString;
use Neo\Modules\Properties\Services\Resources\ProductCategoryResource;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use Neo\Modules\Properties\Services\Resources\PropertyResource;

class ResourceFactory {
    public static function makeIdentifiableProduct(Product $product, OdooClient $client, OdooConfig $config) {
        $property = Property::get($client, $product->shopping_center_id[0]);

        $geolocation = null;

        if ((int)round($property->partner_longitude) !== 0 || (int)round($property->partner_latitude) !== 0) {
            $geolocation = new Geolocation(
                longitude: $property->partner_longitude,
                latitude : $property->partner_latitude,
            );
        }

        return new IdentifiableProduct(
            resourceId: new InventoryResourceId(
                            inventory_id: $config->inventoryID,
                            external_id : $product->getKey(),
                            type        : InventoryResourceType::Product,
                            context     : [
                                              "variant_id" => $product->product_variant_id ? $product->product_variant_id[0] : null,
                                          ]
                        ),
            product   : new ProductResource(
                            name                     : LocalizedString::collection([
                                                                                       new LocalizedString(locale: "en-CA", value: trim($product->name)),
                                                                                       new LocalizedString(locale: "fr-CA", value: trim($product->name)),
                                                                                   ]),
                            type                     : $product->getType(),
                            category_id              : new InventoryResourceId(inventory_id: $config->inventoryID, external_id: $product->categ_id[0], type: InventoryResourceType::ProductCategory, context: []),
                            is_sellable              : $product->active,
                            is_bonus                 : $product->bonus,
                            linked_product_id        : $product->linked_product_id ? new InventoryResourceId(
                                                           inventory_id: $config->inventoryID,
                                                           external_id : $product->linked_product_id[0],
                                                           type        : InventoryResourceType::Product,
                                                       ) : null,
                            quantity                 : $product->nb_screen,
                            price_type               : PriceType::Unit,
                            price                    : $product->list_price,
                            programmatic_price       : $product->list_price,
                            picture_url              : null,
                            loop_configuration       : null,
                            screen_width_px          : 0,
                            screen_height_px         : 0,
                            screen_size_in           : null,
                            screen_type              : null,
                            allowed_media_types      : [],
                            allows_audio             : true,
                            allows_motion            : true,
                            property_id              : new InventoryResourceId(
                                                           inventory_id: $config->inventoryID,
                                                           external_id : $property->getKey(),
                                                           type        : InventoryResourceType::Property
                                                       ),
                            property_name            : trim($property->name),
                            property_type            : null,
                            address                  : $property->getAddress(),
                            geolocation              : $geolocation,
                            timezone                 : null,
                            operating_hours          : null,
                            weekly_traffic           : (int)ceil(($property->annual_traffic / 365) * 7),
                            weekdays_spot_impressions: null,
                        )
        );
    }

    public static function makeIdentifiableProperty(Property $property, OdooConfig $config): PropertyResource {
        return new PropertyResource(
            property_id  : new InventoryResourceId(
                               inventory_id: $config->inventoryID,
                               external_id : $property->getKey(),
                               type        : InventoryResourceType::Property
                           ),
            property_name: trim($property->name)
        );
    }

    public static function makeProductCategory(ProductCategory $category, OdooConfig $config): ProductCategoryResource {
        return new ProductCategoryResource(
            category_id  : new InventoryResourceId(
                               inventory_id: $config->inventoryID,
                               external_id : $category->getKey(),
                               type        : InventoryResourceType::ProductCategory
                           ),
            category_name: $category->display_name
        );
    }
}
