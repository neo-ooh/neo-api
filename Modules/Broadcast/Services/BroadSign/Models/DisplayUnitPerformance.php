<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayUnitPerformance.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\ResourceCastable;
use Neo\Modules\Broadcast\Services\Resources\CampaignLocationPerformance;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Services\API\Parsers\MultipleResourcesParser;

/**
 * Class ReservablePerformance
 *
 * @implements ResourceCastable<CampaignLocationPerformance>
 *
 * @property int $domain_id
 * @property int $id
 * @property int $mobile_interactions
 * @property int $display_unit_id
 * @property int $reservable_id
 * @property int $total
 * @property int $total_impressions
 * @property int $total_interactions
 */
class DisplayUnitPerformance extends BroadSignModel implements ResourceCastable {

    protected static string $unwrapKey = "display_unit_performance";

    protected static array $updatable = [];

    protected static function actions(): array {
        return [
            "getForReservable" => Endpoint::get("/display_unit_performance/v5/by_reservable_id")
                                          ->unwrap(static::$unwrapKey)
                                          ->parser(new MultipleResourcesParser(static::class)),
        ];
    }

    /**
     * List all available performances for the specified reservation
     *
     * @param BroadSignClient $client
     * @param int|string      $reservableId $campaign ID
     * @return Collection<static>
     */
    public static function byReservable(BroadSignClient $client, int|string $reservableId): Collection {
        return static::getForReservable($client, [
            "reservable_id" => $reservableId,
        ]);
    }

    /**
     * @return CampaignLocationPerformance
     */
    public function toResource(): CampaignLocationPerformance {
        return new CampaignLocationPerformance(
            campaign   : new ExternalBroadcasterResourceId(
                             broadcaster_id: $this->getBroadcasterId(),
                             external_id   : $this->reservable_id,
                             type          : ExternalResourceType::Campaign,
                         ),
            location   : new ExternalBroadcasterResourceId(
                             broadcaster_id: $this->getBroadcasterId(),
                             external_id   : $this->display_unit_id,
                             type          : ExternalResourceType::Location,
                         ),
            repetitions: $this->total,
            impressions: $this->total_impressions,
        );
    }
}
