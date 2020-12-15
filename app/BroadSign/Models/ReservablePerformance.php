<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ReservablePerformance.php
 */

namespace Neo\BroadSign\Models;

use ArrayAccess;
use Illuminate\Support\Collection;
use Neo\BroadSign\Endpoint;

/**
 * Class ReservablePerformance
 *
 * @package Neo\BroadSign\Models
 *
 * @property int    domain_id
 * @property int    id
 * @property int    mobile_interactions
 * @property string played_on
 * @property int    reservable_id
 * @property int    total
 * @property int    total_impressions
 * @property int    total_interactions
 *
 * @method static Collection manyByReservable(array $params)
 */
class ReservablePerformance extends BroadSignModel {

    protected static string $unwrapKey = "campaign_performance";

    protected static array $updatable = [];

    protected static function actions(): array {
        return [
            "manyByReservable" => Endpoint::get("/campaign_performance/v6/many_by_reservable_id")->multiple()->cache(3600*8),
        ];
    }

    /**
     * List all available performances for each of the specified reservations
     * @param array $reservableIds
     * @return Collection
     */
    public static function byReservable(array $reservableIds) {
        return static::manyByReservable([
            "reservable_ids" => implode(", ", $reservableIds),
        ]);
    }
}
