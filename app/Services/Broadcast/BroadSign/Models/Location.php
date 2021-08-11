<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Location.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Services\Broadcast\BroadSign\API\Parsers\SingleResourcesParser;
use phpDocumentor\Reflection\Types\Array_;

/**
 * Class ActorsLocations
 *
 * @package Neo\BroadSign\Models
 *
 * @property int       $id
 * @property string    $name
 * @property bool      $active
 * @property int       $container_id
 * @property int       $domain_id
 * @property int       $display_unit_type_id
 * @property string    $timezone
 * @property int       $host_screen_count
 * @property bool      $enforce_day_parts
 * @property string    $zipcode
 * @property string    $address
 * @property string    $external_id
 * @property bool      $enforce_screen_controls
 * @property int       $geolocation
 * @property bool      $export_enabled
 * @property int       $export_first_enabled_tm
 * @property int       $export_first_enabled_by_user_id
 * @property int       $export_retired_on_tm
 * @property int       $export_retired_by_user_id
 * @property int       $virtual_host_screen_count
 * @property string    $virtual_id
 * @property int       $bmb_host_id
 *
 * @property Container container
 *
 * @method static Collection all(BroadsignClient $client)
 * @method static Location get(BroadsignClient $client, int $displayUnitId)
 * @method static Collection byReservable(BroadsignClient $client, array $params)
 */
class Location extends BroadSignModel {
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
     * Get the location's container
     *
     * @return Container|null
     */
    public function getContainer(): ?Container {
        if ($this->container_id === 0) {
            return null;
        }

        return Container::get($this->api, $this->container_id);
    }

    public static function inContainer(BroadsignClient $client, int $containerId) {
        return static::byContainer($client, ["container_id" => $containerId]);
    }
}
