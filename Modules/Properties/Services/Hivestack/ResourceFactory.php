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

use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Services\Hivestack\Models\Site;
use Neo\Modules\Properties\Services\Hivestack\Models\Unit;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\Geolocation;
use Neo\Modules\Properties\Services\Resources\IdentifiableProduct;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Modules\Properties\Services\Resources\LocalizedString;
use Neo\Modules\Properties\Services\Resources\LoopConfiguration;
use Neo\Modules\Properties\Services\Resources\ProductResource;
use Neo\Modules\Properties\Services\Resources\PropertyResource;

class ResourceFactory {
    /**
     * @param Unit            $unit
     * @param HivestackConfig $config
     * @return IdentifiableProduct
     */
    public static function makeIdentifiableProduct(Unit $unit, HivestackConfig $config): IdentifiableProduct {
        $site     = Site::find($config->getClient(), $unit->site_id);
        $unitName = static::trimStart(trim($unit->name), trim($site->name) . " - ");

        return new IdentifiableProduct(
            resourceId: $unit->toInventoryResourceId($config->inventoryID),
            product   : new ProductResource(
                            name                     : LocalizedString::collection([new LocalizedString(locale: "en-CA", value: $unitName)]),
                            type                     : ProductType::Digital,
                            category_id              : null,
                            is_sellable              : $unit->active,
                            is_bonus                 : false,
                            linked_product_id        : null,
                            quantity                 : 1,
                            price_type               : PriceType::CPM,
                            price                    : $unit->floor_cpm,
                            picture_url              : null,
                            loop_configuration       : new LoopConfiguration(
                                                           loop_length_ms: $unit->loop_length * 1000,
                                                           spot_length_ms: $unit->spot_length * 1000
                                                       ),
                            screen_width_px          : $unit->screen_width,
                            screen_height_px         : $unit->screen_height,
                            screen_size_in           : $unit->physical_screen_height_cm > 0 && $unit->physical_screen_width_cm > 0 ? hypot($unit->physical_screen_width_cm, $unit->physical_screen_height_cm) / 2.54 : null,
                            screen_type              : null,
                            allowed_media_types      : array_filter([
                                                                        $unit->allow_image ? MediaType::Image : null,
                                                                        $unit->allow_video ? MediaType::Video : null,
                                                                        $unit->allow_html ? MediaType::HTML : null,
                                                                    ], fn(MediaType|null $type) => $type !== null),
                            allows_audio             : false,
                            allows_motion            : true,
                            property_id              : $site->toInventoryResourceId($config->inventoryID),
                            property_name            : trim($site->name),
                            property_type            : $unit->mediatype_id ? new InventoryResourceId(
                                                           inventory_id: $config->inventoryID,
                                                           external_id : $unit->mediatype_id,
                                                           type        : InventoryResourceType::PropertyType,
                                                           context     : []
                                                       ) : null,
                            address                  : null,
                            geolocation              : new Geolocation(
                                                           longitude: $site->longitude,
                                                           latitude : $site->latitude
                                                       ),
                            timezone                 : $unit->timezone,
                            // TODO
                            operating_hours          : null,
                            weekly_traffic           : 0,
                            weekdays_spot_impressions: null,
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

    /**
     * @param string    $str           Original string
     * @param string    $needle        String to trim from the beginning of $str
     * @param bool|true $caseSensitive Perform case-sensitive matching, defaults to true
     * @return string Trimmed string
     *
     * @author Bas
     * @link   https://stackoverflow.com/a/32739088
     */
    protected static function trimStart($str, $needle, $caseSensitive = true) {
        $strPosFunction = $caseSensitive ? "strpos" : "stripos";
        if ($strPosFunction($str, $needle) === 0) {
            $str = substr($str, strlen($needle));
        }
        return $str;
    }
}
