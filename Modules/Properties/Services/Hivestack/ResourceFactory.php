<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceFactory.php
 */

namespace Neo\Modules\Properties\Services\Hivestack;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Client\RequestException;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Services\Hivestack\Models\Site;
use Neo\Modules\Properties\Services\Hivestack\Models\Unit;
use Neo\Modules\Properties\Services\Resources\Geolocation;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\LocalizedString;
use Neo\Modules\Properties\Services\Resources\LoopConfiguration;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use Neo\Modules\Properties\Services\Resources\PropertyResource;

class ResourceFactory {
    /**
     * @param Unit            $unit
     * @param HivestackConfig $config
     * @return IdentifiableProduct
     * @throws GuzzleException
     * @throws RequestException
     */
    public static function makeIdentifiableProduct(Unit $unit, HivestackConfig $config): IdentifiableProduct {
        $site = Site::find($config->getClient(), $unit->site_id);

        return new IdentifiableProduct(
            resourceId: $unit->toInventoryResourceId($config->inventoryID),
            product   : new ProductResource(
                            name                  : LocalizedString::collection([new LocalizedString(locale: "en-CA", value: trim($unit->name))]),
                            type                  : ProductType::Digital,
                            category_id           : null,
                            is_bonus              : false,
                            linked_product_id     : null,
                            quantity              : 1,
                            price_type            : PriceType::CPM,
                            price                 : $unit->floor_cpm,
                            picture_url           : null,
                            loop_configuration    : new LoopConfiguration(
                                                        loop_length_ms: $unit->loop_length * 1000,
                                                        spot_length_ms: $unit->spot_length * 1000
                                                    ),
                            allow_audio           : true,
                            screen_width_px       : $unit->screen_width,
                            screen_height_px      : $unit->screen_height,
                            allowed_creative_types: [
                                                        // TODO
                                                    ],
                            property_id           : $site->toInventoryResourceId($config->inventoryID),
                            property_name         : trim($site->name),
                            address               : null,
                            geolocation           : new Geolocation(
                                                        longitude: $site->longitude,
                                                        latitude : $site->latitude
                                                    ),
                            // TODO
                            operating_hours       : null,
                            weekly_traffic        : 0
                        )
        );
    }

    /**
     * @param Site            $site
     * @param HivestackConfig $config
     * @return PropertyResource
     */
    public static function makeIdentifiableProperty(Site $site, HivestackConfig $config): PropertyResource {
        return new PropertyResource(
            property_id  : $site->toInventoryResourceId($config->inventoryID),
            property_name: $site->name,
        );
    }
}
