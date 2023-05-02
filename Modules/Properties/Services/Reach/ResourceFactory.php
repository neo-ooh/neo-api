<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceFactory.php
 */

namespace Neo\Modules\Properties\Services\Reach;

use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Services\Reach\Models\Attributes\NamedIdentityAttribute;
use Neo\Modules\Properties\Services\Reach\Models\Screen;
use Neo\Modules\Properties\Services\Resources\Geolocation;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\LocalizedString;
use Neo\Modules\Properties\Services\Resources\ProductResource;

class ResourceFactory {
    /**
     * @param Screen      $screen
     * @param ReachConfig $config
     * @return IdentifiableProduct
     */
    public static function makeIdentifiableProduct(Screen $screen, ReachConfig $config): IdentifiableProduct {
        $resourceID                                  = $screen->toInventoryResourceId($config->inventoryID);
        $resourceID->context["location_external_id"] = explode(":", $screen->device_id)[1];
        $resourceID->context["venue_type_id"]        = $screen->venue_types[0]->id ?? 0;

        return new IdentifiableProduct(
            resourceId: $resourceID,
            product   : new ProductResource(
                            name                     : LocalizedString::collection([new LocalizedString(locale: "en-CA", value: $screen->name)]),
                            type                     : ProductType::Digital,
                            category_id              : null,
                            is_sellable              : $screen->is_active,
                            is_bonus                 : false,
                            linked_product_id        : null,
                            quantity                 : 1,
                            price_type               : PriceType::CPM,
                            price                    : $screen->floor_cpm ?? 0,
                            picture_url              : null,
                            loop_configuration       : null,
                            screen_width_px          : $screen->resolution->width,
                            screen_height_px         : $screen->resolution->height,
                            allowed_media_types      : $screen->allowed_ad_types->map(fn(NamedIdentityAttribute $adType) => match ($adType->id) {
                                                           1 => MediaType::Image,
                                                           2 => MediaType::Video,
                                                           3 => MediaType::Audio,
                                                           4 => MediaType::HTML,
                                                       })->all(),
                            allows_audio             : $screen->is_audio,
                            property_id              : null,
                            property_name            : "",
                            address                  : null, //TODO: Address parsing
                            geolocation              : new Geolocation(
                                                           longitude: $screen->longitude,
                                                           latitude : $screen->latitude
                                                       ),
                            timezone                 : $screen->time_zone->name,
                            // TODO
                            operating_hours          : null,
                            weekly_traffic           : 0,
                            weekdays_spot_impressions: null,
                        )
        );
    }
}
