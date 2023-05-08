<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceFactory.php
 */

namespace Neo\Modules\Properties\Services\Vistar;

use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Services\Reach\Models\Screen;
use Neo\Modules\Properties\Services\Resources\Geolocation;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\LocalizedString;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use Neo\Modules\Properties\Services\Vistar\Models\Venue;

class ResourceFactory {
    /**
     * @param Screen       $screen
     * @param VistarConfig $config
     * @return IdentifiableProduct
     */
    public static function makeIdentifiableProduct(Venue $venue, VistarConfig $config): IdentifiableProduct {
        $resourceID = $venue->toInventoryResourceId($config->inventoryID);

        $propertyId = null;
        $productId  = null;
        $tokens     = [];

        if (preg_match("/^connect_([0-9]+)_([0-9]+)$/", trim($venue->partner_venue_id), $tokens)) {
            $propertyId = $tokens[1];
            $productId  = $tokens[2];
        }

        return new IdentifiableProduct(
            resourceId: $resourceID,
            product   : new ProductResource(
                            name                     : LocalizedString::collection([new LocalizedString(locale: "en-CA", value: $venue->name)]),
                            type                     : ProductType::Digital,
                            category_id              : null,
                            is_sellable              : true,
                            is_bonus                 : false,
                            linked_product_id        : null,
                            quantity                 : 1,
                            price_type               : PriceType::CPM,
                            price                    : $venue->cpm_floor_cents / 100,
                            picture_url              : null,
                            loop_configuration       : null,
                            screen_width_px          : $venue->width_px,
                            screen_height_px         : $venue->height_px,
                            allowed_media_types      : array_filter([
                                                                        $venue->static_supported ? MediaType::Image : null,
                                                                        $venue->video_supported ? MediaType::Video : null,
                                                                    ], fn(MediaType|null $type) => $type !== null),
                            allows_audio             : false,
                            property_id              : null,
                            property_name            : "",
                            address                  : null, //TODO: Address parsing
                            geolocation              : new Geolocation(
                                                           longitude: $venue->longitude,
                                                           latitude : $venue->latitude
                                                       ),
                            timezone                 : null,
                            // TODO
                            operating_hours          : null,
                            weekly_traffic           : 0,
                            weekdays_spot_impressions: null,
                            product_connect_id       : $productId,
                            property_connect_id      : $propertyId
                        )
        );
    }
}
