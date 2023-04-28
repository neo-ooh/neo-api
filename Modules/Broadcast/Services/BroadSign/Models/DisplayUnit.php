<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayUnit.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Modules\Broadcast\Services\ResourceCastable;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Broadcast\Services\Resources\Location as LocationResource;
use Neo\Services\API\Parsers\MultipleResourcesParser;

/**
 * Class Location
 *
 * @implements ResourceCastable<LocationResource>
 *
 * @property int    $id
 * @property string $name
 * @property bool   $active
 * @property int    $container_id
 * @property int    $domain_id
 * @property int    $display_unit_type_id
 * @property string $timezone
 * @property int    $host_screen_count
 * @property bool   $enforce_day_parts
 * @property string $zipcode
 * @property string $address
 * @property string $external_id
 * @property bool   $enforce_screen_controls
 * @property string $geolocation
 * @property bool   $export_enabled
 * @property int    $export_first_enabled_tm
 * @property int    $export_first_enabled_by_user_id
 * @property int    $export_retired_on_tm
 * @property int    $export_retired_by_user_id
 * @property int    $virtual_host_screen_count
 * @property string $virtual_id
 * @property int    $bmb_host_id
 *
 * @method static Collection<static> all(BroadSignClient $client)
 * @method static static get(BroadSignClient $client, int $displayUnitId)
 * @method static Collection<static> byContainer(BroadSignClient $client, array $params)
 */
class DisplayUnit extends BroadSignModel implements ResourceCastable {
    protected static string $unwrapKey = "display_unit";

    protected static function actions(): array {
        return [
            "all"          => Endpoint::get("/display_unit/v12")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class)),
            "get"          => Endpoint::get("/display_unit/v12/{id}")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new SingleResourcesParser(static::class)),
            "byContainer"  => Endpoint::get("/display_unit/v12/by_container")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class)),
            "byReservable" => Endpoint::get("/display_unit/v12/by_reservable")
                                      ->domain(false)
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class)),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @param BroadSignClient $client
     * @param int             $containerId
     * @return Collection<static>
     */
    public static function inContainer(BroadSignClient $client, int $containerId): Collection {
        return static::byContainer($client, ["container_id" => $containerId]);
    }

    /**
     * List display units associated with a reservable
     *
     * @param BroadSignClient $client
     * @param int             $reservableId
     * @return Collection<static>
     */
    public static function byReservable(BroadSignClient $client, int $reservableId): Collection {
        return (new static($client))->callAction("byReservable", ["reservable_id" => $reservableId]);
    }

    /*
    |--------------------------------------------------------------------------
    |
    |--------------------------------------------------------------------------
    */

    /**
     * @return LocationResource
     */
    public function toResource(): LocationResource {
        // Parse address
        if (preg_match('/(^\d*)\s([.\-\w\s]+),\s*([.\-\w\s]+),\s*([A-Z]{2})\s(\w\d\w\s*\d\w\d)/iu', $this->address, $matches)) {
            $address = trim($matches[1]);
            if (!trim($matches[2])) {
                $address .= " " . trim($matches[2]);
            }

            $city     = trim($matches[3]);
            $province = trim($matches[4]);
            $country  = "CA";
            $zipcode  = str_replace(" ", "", trim($matches[5]));
        } else {
            $address  = null;
            $city     = null;
            $province = null;
            $country  = null;
            $zipcode  = null;
        }

        [$lng, $lat] = strlen($this->geolocation) > 0 ? explode(",", substr($this->geolocation, 1, -1)) : [0, 0];

        return new LocationResource(
            broadcaster_id          : $this->getBroadcasterId(),
            external_id             : $this->getKey(),
            enabled                 : $this->active,
            name                    : $this->name,
            external_display_type_id: new ExternalBroadcasterResourceId(
                                          broadcaster_id: $this->getBroadcasterId(),
                                          external_id   : $this->display_unit_type_id,
                                          type          : ExternalResourceType::DisplayType,
                                      ),
            container_id            : new ExternalBroadcasterResourceId(
                                          broadcaster_id: $this->getBroadcasterId(),
                                          external_id   : $this->container_id,
                                          type          : ExternalResourceType::Container,
                                      ),
            address                 : $address,
            city                    : $city,
            province                : $province,
            country                 : $country,
            zipcode                 : $zipcode,
            lat                     : $lat,
            lng                     : $lng,
        );
    }
}
