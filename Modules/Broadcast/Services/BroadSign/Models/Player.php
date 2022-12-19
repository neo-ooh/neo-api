<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Player.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Support\Collection;
use League\Uri\Uri;
use League\Uri\UriModifier;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Modules\Broadcast\Services\ResourceCastable;
use Neo\Modules\Broadcast\Services\Resources\Player as PlayerResource;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * Class Player
 *
 * @implements ResourceCastable<PlayerResource>
 *
 * @property bool   $active
 * @property int    $config_profile_bag_id
 * @property int    $container_id
 * @property string $custom_unique_id
 * @property string $db_pickup_tm_utc
 * @property bool   $discovery_status
 * @property int    $display_unit_id
 * @property int    $domain_id
 * @property string $geolocation
 * @property int    $id
 * @property string $name
 * @property int    $nscreens
 * @property string $primary_mac_address
 * @property string $public_key_fingerprint
 * @property string $remote_clear_db_tm_utc
 * @property string $remote_reboot_tm_utc
 * @property string $secondary_mac_address
 * @property int    $volume
 *
 * @method static Collection<static> all(BroadSignClient $client)
 * @method static static get(BroadSignClient $client, int $playerID)
 * @method static Collection<static> get_multiple(BroadSignClient $client, array $payload)
 */
class Player extends BroadSignModel implements ResourceCastable {
    protected static string $unwrapKey = "host";

    protected static function actions(): array {
        return [
            "all"          => Endpoint::get("/host/v17")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class)),
            "get"          => Endpoint::get("/host/v17/{id}")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new SingleResourcesParser(static::class)),
            "get_multiple" => Endpoint::get("/host/v17/by_id")
                                      ->unwrap(static::$unwrapKey)
                                      ->parser(new MultipleResourcesParser(static::class)),
            "request"      => Endpoint::post("/host/v17/push_request")
                                      ->unwrap(static::$unwrapKey),
        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Custom Mechanisms
    |--------------------------------------------------------------------------
    */

    /**
     * Pull multiple players/hosts at once using their ids
     *
     * @param BroadSignClient $client
     * @param array           $playersIds
     * @return Collection<static>
     */
    public static function getMultiple(BroadSignClient $client, array $playersIds): Collection {
        return static::get_multiple($client, ["ids" => implode(",", $playersIds)]) ?? collect();
    }

    /**
     * Send a request to the player for a burst of screenshots, using the specified parameters.
     *
     * @param string $burstID      ID of the burst request, used to build the callback URL
     * @param string $responseUri
     * @param int    $scale        1 to 100, scale factor of the screenshots
     * @param int    $duration_ms  in ms, how long should the burst last
     * @param int    $frequency_ms in ms, number of milliseconds between bursts
     */
    public function requestScreenshotsBurst(string $burstID, string $responseUri, int $scale, int $duration_ms, int $frequency_ms): void {
        // Add the player ID to the response uri
        $uri = Uri::createFromString($responseUri);
        $uri = UriModifier::appendQuery($uri, "player_id={$this->getKey()}");

        $this->sendRequest([
            "rc" => [
                "version"            => 1,
                "id"                 => $burstID,
                "action"             => "screenshot_request",
                //                "dest_url"           => config("app.url") . "/v1/broadsign/burst_callback/" . $burstID . "?player_id=" . $this->id,
                "dest_url"           => (string)$uri,
                "scale_factor"       => $scale,
                "burst_duration_ms"  => $duration_ms,
                "burst_frequency_ms" => $frequency_ms,
            ],
        ]);
    }

    /**
     * Request the information about the current content being played by the player.
     *
     * @param int $frameId ID of the frame from which to request information
     */
    public function nowPlaying(int $frameId = 0): void {
        $this->sendRequest([
            "rc" => [
                "version"  => 3,
                "id"       => (string)$this->id,
                "action"   => "now_playing",
                "frame_id" => $frameId,
            ],
        ]);
    }

    /**
     * Request the information about the current content being played by the player.
     */
    public function forceUpdatePlaylist() {
        return $this->sendRequest([
            "rc" => [
                "version" => 1,
                "id"      => (string)$this->id,
                "action"  => "poll_request",
            ],
        ]);
    }

    public function sendRequest(array $payload) {
        return $this->callAction("request",
            [
                "player_id"    => $this->id,
                "request_json" => $payload,
            ]);
    }


    /**
     * @throws UnknownProperties
     */
    public function toResource(): PlayerResource {
        return new PlayerResource([
            "broadcaster_id" => $this->getBroadcasterId(),
            "enabled"        => $this->active,
            "external_id"    => $this->getKey(),
            "name"           => $this->name,
            "location_id"    => [
                "broadcaster_id" => $this->getBroadcasterId(),
                "type"           => ExternalResourceType::Location,
                "external_id"    => $this->display_unit_id,
            ],
        ]);
    }
}
