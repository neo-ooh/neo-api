<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Unit.php
 */

namespace Neo\Modules\Properties\Services\Hivestack\Models;

use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

/**
 * @property int      $unit_id
 * @property string   $uuid
 * @property boolean  $active
 * @property int      $owner_id
 * @property int      $site_id         required
 * @property string   $external_id     required
 * @property string   $name            required
 * @property string   $description     required
 * @property string   $image_uri
 * @property int      $network_id      required
 *
 * @property string   $timezone        required - A timezone in standard database format
 * @property string   $operating_hours A weekly daypart of when a display is activated for ad delivery represented by 1s and 0s
 *           for each hour of the the week beginning on Monday at 12AM
 *
 * @property string   $facing_direction
 * @property float    $floor_cpm
 * @property int      $location_id
 *
 * @property float    $longitude       required
 * @property float    $latitude        required
 *
 * @property int      $loop_length     required - seconds
 * @property int      $spot_length     seconds
 * @property int      $max_spot_length seconds
 * @property int      $min_spot_length seconds
 * @property int      $mediatype_id
 * @property int      $mediatype_name
 *
 * @property int      $min_seconds_between_ad_domain_plays
 * @property int      $min_seconds_between_creative_category_plays
 *
 * @property int      $physical_screen_height_cm
 * @property int      $physical_screen_widtg_cm
 *
 * @property int      $screen_height   required - pixels
 * @property int      $screen_width    required - pixels
 *
 * @property boolean  $allow_html
 * @property boolean  $allow_image
 * @property boolean  $allow_video
 * @property boolean  $allow_zip
 * @property boolean  $allow_audio
 *
 * @property boolean  $available_for_adserver
 * @property boolean  $available_for_deals
 * @property boolean  $available_for_open_exchange
 * @property boolean  $available_for_store_front
 * @property string[] $blacklist_unique_advertiser_ids
 * @property boolean  enable_strict_iab_blacklisting
 * @property boolean  enable_strict_iab_frequency_capping
 *
 * @property string   $created_on_utc
 * @property string   $modified_on_utc
 */
class Unit extends HivestackModel {
    public string $key = "unit_id";

    public function toInventoryResourceId(int $inventoryId): InventoryResourceId {
        return new InventoryResourceId(
            inventory_id: $inventoryId,
            external_id : $this->unit_id,
            type        : InventoryResourceType::Product,
            context     : [
                              "network_id" => $this->network_id,
                          ],
        );
    }
}
