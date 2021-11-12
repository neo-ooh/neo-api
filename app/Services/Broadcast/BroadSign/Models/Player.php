<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Player.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Services\Broadcast\BroadSign\API\Parsers\SingleResourcesParser;

/**
 * Class Player
 *
 * @package Neo\BroadSign\Models
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
 * @method static Collection all(BroadsignClient $client)
 * @method static Player get(BroadsignClient $client, int $playerID)
 */
class Player extends BroadSignModel {
    protected static string $unwrapKey = "host";

    protected static function actions(): array {
        return [
            "all"     => Endpoint::get("/host/v17")
                                 ->unwrap(static::$unwrapKey)
                                 ->parser(new MultipleResourcesParser(static::class)),
            "get"     => Endpoint::get("/host/v17/{id}")
                                 ->unwrap(static::$unwrapKey)
                                 ->parser(new SingleResourcesParser(static::class)),
            "request" => Endpoint::post("/host/v17/push_request")
                                 ->unwrap(static::$unwrapKey),
        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Custom Mechanisms
    |--------------------------------------------------------------------------
    */

    /**
     * Send a request to the player for a burst of screenshots, using the specified parameters.
     *
     * @param int $burstID   ID of the burst request, used to build the callback URL
     * @param int $scale     1 to 100, scale factor of the screenshots
     * @param int $duration  in ms, how long should the burst last
     * @param int $frequency in ms, number of milliseconds between bursts
     *
     */
    public function requestScreenshotsBurst(int $burstID, int $scale, int $duration, int $frequency): void {
        $this->sendRequest([
            "rc" => [
                "version"            => 1,
                "id"                 => (string)$burstID,
                "action"             => "screenshot_request",
                "dest_url"           => config("app.url") . "/v1/broadsign/burst_callback/" . $burstID . "?player_id=" . $this->id,
                "scale_factor"       => $scale,
                "burst_duration_ms"  => $duration,
                "burst_frequency_ms" => $frequency,
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
     *
     * @param int $frameId ID of the frame from which to request information
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
                "request_json" => $payload
            ]);
    }
}
