<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Venue.php
 */

namespace Neo\Modules\Properties\Services\Vistar\Models;

use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Modules\Properties\Services\Vistar\Models\Attributes\VenueImpressions;
use Neo\Modules\Properties\Services\Vistar\Models\Attributes\VenueOperatingMinutes;

/**
 * @property string           $id
 * @property string           $network_id
 * @property string           $name
 * @property string           $partner_venue_id
 * @property int              $cpm_floor_cents
 * @property float            $longitude
 * @property float            $latitude
 * @property array            $advertiser_restrictions
 * @property array            $creative_category_restrictions
 * @property string           $address
 *
 * @property int              $venue_type_id
 * @property string           $venue_type
 *
 * @property VenueImpressions $impressions
 * @property string           $registration_id // Player ID
 * @property int              $static_duration_seconds
 * @property bool             $static_supported
 * @property bool             $min_duration_ms
 * @property bool             $max_duration_ms
 * @property bool             $video_supported
 * @property int              $height_px
 * @property int              $width_px
 * @property array            $tag_restrictions
 * @property array            $operating_minutes
 * @property string           $activation_date
 * @property array            $excluded_buy_types
 * @property string           $location_id
 * @property bool             $cortex_supported
 * @property string|null      $industry_id
 */
class Venue extends VistarModel {
    public string $key = "id";

    public string $endpoint = "/seller/v1/venues";

    public string $slug = "venues";

    protected array $casts = [
        "impressions"       => VenueImpressions::class,
        "operating_minutes" => VenueOperatingMinutes::class,
    ];

    public function toInventoryResourceId(int $inventoryId): InventoryResourceId {
        return new InventoryResourceId(
            inventory_id: $inventoryId,
            external_id : $this->getKey(),
            type        : InventoryResourceType::Product,
            context     : [
                              "player_external_id" => $this->registration_id,
                              "network_id"         => $this->network_id,
                          ],
        );
    }
}
