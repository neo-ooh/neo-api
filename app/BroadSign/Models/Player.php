<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - Player.php
 */

namespace Neo\BroadSign\Models;

use Illuminate\Support\Collection;
use JsonException;
use Neo\BroadSign\BroadSign;
use Neo\BroadSign\Endpoint;

/**
 * Class Player
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   active
 * @property int    config_profile_bag_id
 * @property int    container_id
 * @property string custom_unique_id
 * @property string db_pickup_tm_utc
 * @property bool   discovery_status
 * @property int    display_unit_id
 * @property int    domain_id
 * @property string geolocation
 * @property int    id
 * @property string name
 * @property int     nscreens
 * @property string  primary_mac_address
 * @property string  public_key_fingerprint
 * @property string  remote_clear_db_tm_utc
 * @property string  remote_reboot_tm_utc
 * @property string  secondary_mac_address
 * @property int     volume
 *
 * @method static Collection all()
 * @method static Player get(int $playerID)
 */
class Player extends BroadSignModel {
    protected static string $unwrapKey = "host";

    protected static function actions (): array {
        return [
            "all"     => Endpoint::get("/host/v17")->multiple(),
            "get"     => Endpoint::get("/host/v17/{id}"),
            "request" => Endpoint::post("/host/v17/push_request")->id(),
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
     * @throws JsonException
     */
    public function requestScreenshotsBurst (int $burstID, int $scale, int $duration, int $frequency): void {
        $this->sendRequest([
            "rc" => [
                "version"            => 1,
                "id"                 => (string)$burstID,
                "action"             => "screenshot_request",
                "dest_url"           => config("app.url") . "/v1/broadsign/burst_callback/" . $burstID,
                "scale_factor"       => $scale,
                "burst_duration_ms"  => $duration,
                "burst_frequency_ms" => $frequency,
            ],
        ]);
    }

    public function sendRequest (array $payload): void {
        $this->callAction("request",
            [
                "domain_id"    => BroadSign::getDefaults()['domain_id'],
                "player_id"    => $this->id,
                "request_json" => json_encode($payload,
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
    }
}
