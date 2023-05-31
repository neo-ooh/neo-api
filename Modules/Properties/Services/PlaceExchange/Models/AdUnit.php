<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdUnit.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange\Models;

use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitAsset;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitAuction;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitLocation;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitMeasurement;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitPlanning;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitRestrictions;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitSlot;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitStatus;
use Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes\AdUnitVenue;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

/**
 * @property array{w: int, h: int}[]           $ad_formats       width and height in pixels
 * @property array{wratio: int, hratio: int}[] $aspect_ratios    e.g. 16:9
 * @property AdUnitAsset|null                  $asset
 * @property AdUnitAuction|null                $auction
 * @property int                               $created_by
 * @property string[]                          $eids
 * @property null                              $ext
 * @property string                            $id
 * @property int                               $integration_type //0: API, 1: Light, 2: Vast, 3: BroadSign
 * @property string[]                          $keywords
 * @property string                            $lastmod
 * @property AdUnitLocation|null               $location
 * @property AdUnitMeasurement|null            $measurement
 * @property string                            $name
 * @property string|null                       $network_id
 * @property string                            $network_name
 * @property string                            $notes
 * @property string                            $owned_by
 * @property array                             $placements
 * @property AdUnitPlanning                    $planning
 * @property int|null                          $private_auction  // Inactive -> Open to all
 * @property AdUnitRestrictions|null           $restrictions
 * @property AdUnitSlot                        $slot
 * @property string|null                       $start_date       // YYYY-MM-DD
 * @property AdUnitStatus                      $status           1: Pending; 2: On-Demand; 3: Live; 4: Inactive; 5:
 *           Decommissioned;
 *           6: In-Review
 * @property string                            $ts
 * @property AdUnitVenue                       $venue
 */
class AdUnit extends PlaceExchangeModel {
    public string $key = "name";

    protected array $casts = [
        "asset"        => AdUnitAsset::class,
        "auction"      => AdUnitAuction::class,
        "location"     => AdUnitLocation::class,
        "measurement"  => AdUnitMeasurement::class,
        "planning"     => AdUnitPlanning::class,
        "restrictions" => AdUnitRestrictions::class,
        "slot"         => AdUnitSlot::class,
        "status"       => AdUnitStatus::class,
        "venue"        => AdUnitVenue::class,
    ];

    public function toInventoryResourceId(int $inventoryId): InventoryResourceId {
        return new InventoryResourceId(
            inventory_id: $inventoryId,
            external_id : $this->getKey(),
            type        : InventoryResourceType::Product,
            context     : [
                              "network_id" => $this->network_id,
                          ],
        );
    }
}
