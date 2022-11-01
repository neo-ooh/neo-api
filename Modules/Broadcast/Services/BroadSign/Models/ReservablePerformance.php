<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReservablePerformance.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\ResourceCastable;
use Neo\Modules\Broadcast\Services\Resources\CampaignPerformance as CampaignPerformanceResource;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * Class ReservablePerformance
 *
 * @implements ResourceCastable<CampaignPerformanceResource>
 *
 * @property int    $domain_id
 * @property int    $id
 * @property int    $mobile_interactions
 * @property string $played_on
 * @property int    $reservable_id
 * @property int    $total
 * @property int    $total_impressions
 * @property int    $total_interactions
 *
 * @method static Collection manyByReservable(BroadSignClient $client, array $params)
 */
class ReservablePerformance extends BroadSignModel implements ResourceCastable {

    protected static string $unwrapKey = "campaign_performance";

    protected static array $updatable = [];

    protected static function actions(): array {
        return [
            "manyByReservable" => Endpoint::get("/campaign_performance/v6/many_by_reservable_id")
                                          ->unwrap(static::$unwrapKey)
                                          ->parser(new MultipleResourcesParser(static::class)),
        ];
    }

    /**
     * List all available performances for each of the specified reservations
     *
     * @param BroadSignClient   $client
     * @param array<int|string> $reservableIds
     * @return Collection<static>
     */
    public static function byReservable(BroadSignClient $client, array $reservableIds): Collection {
        return static::manyByReservable($client, [
            "reservable_ids" => implode(", ", $reservableIds),
        ]);
    }

    /**
     * @throws UnknownProperties
     */
    public function toResource(): CampaignPerformanceResource {
        return new CampaignPerformanceResource([
            "campaign"    => [
                "type"           => ExternalResourceType::Campaign,
                "broadcaster_id" => $this->getBroadcasterId(),
                "external_id"    => $this->reservable_id,
            ],
            "date"        => $this->played_on,
            "repetitions" => $this->total,
            "impressions" => $this->total_impressions,
        ]);
    }
}
