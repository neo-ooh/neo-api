<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceFactory.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange;

use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Services\PlaceExchange\Models\AdUnit;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitStatus;
use Neo\Modules\Properties\Services\Resources\Address;
use Neo\Modules\Properties\Services\Resources\City;
use Neo\Modules\Properties\Services\Resources\Geolocation;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\LocalizedString;
use Neo\Modules\Properties\Services\Resources\LoopConfiguration;
use Neo\Modules\Properties\Services\Resources\ProductResource;

class ResourceFactory {
    /**
     * @param AdUnit              $adUnit
     * @param PlaceExchangeConfig $config
     * @return IdentifiableProduct
     */
    public static function makeIdentifiableProduct(AdUnit $adUnit, PlaceExchangeConfig $config): IdentifiableProduct {
        return new IdentifiableProduct(
            resourceId: $adUnit->toInventoryResourceId($config->inventoryID),
            product   : new ProductResource(
                            name                     : LocalizedString::collection([new LocalizedString(locale: "en-CA", value: $adUnit->name)]),
                            type                     : ProductType::Digital,
                            category_id              : null,
                            is_sellable              : $adUnit->status === AdUnitStatus::Live,
                            is_bonus                 : false,
                            linked_product_id        : null,
                            quantity                 : $adUnit->asset->screen_count,
                            price_type               : PriceType::CPM,
                            price                    : $adUnit->auction->bidfloor,
                            picture_url              : $adUnit->asset->image_url,
                            loop_configuration       : new LoopConfiguration(
                                                           loop_length_ms: 0,// unknown
                                                           spot_length_ms: $adUnit->slot->max_duration * 1_000,
                                                       ),
                            screen_width_px          : $adUnit->slot->w,
                            screen_height_px         : $adUnit->slot->h,
                            allowed_media_types      : array_filter([
                                                                        $adUnit->asset->capability->video ? MediaType::Video : null,
                                                                        $adUnit->asset->capability->banner ? MediaType::Image : null,
                                                                    ], fn(MediaType|null $type) => $type !== null),
                            allows_audio             : $adUnit->asset->capability->audio,
                            property_id              : null,
                            property_name            : $adUnit->venue->name,
                            address                  : new Address(
                                                           line_1 : "",
                                                           line_2 : "",
                                                           city   : new City(
                                                                        name         : $adUnit->location->city,
                                                                        province_slug: $adUnit->location->region,
                                                                    ),
                                                           zipcode: $adUnit->location->zip,
                                                           full   : $adUnit->venue->address
                                                       ),
                            geolocation              : new Geolocation(
                                                           longitude: $adUnit->location->lon,
                                                           latitude : $adUnit->location->lat
                                                       ),
                            timezone                 : null,
                            operating_hours          : null,
                            weekly_traffic           : 0,
                            weekdays_spot_impressions: null,
                        )
        );
    }
}
