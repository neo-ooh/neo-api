<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Player.php
 */

namespace Neo\Services\Broadcast\PiSignage\Models;

use Illuminate\Support\Collection;
use Neo\Services\API\Endpoint;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\PiSignage\API\PiSignageClient;

/**
 * Class Group
 *
 * @package  Neo\Services\Broadcast\PiSignage\Models
 *
 * @property string                           $_id
 * @property string                           $name            Player name or location
 * @property string                           $TZ              example: Asia/Calcutta
 * @property string                           $cpuSerialNumber 16-digit serial number
 * @property bool                             $managed         Whether this player is being managed by the pisignage.com service
 * @property string                           $configLocation  Location of the player
 * @property array<string>                    $labels
 * @property array{_id: string, name: string} $group
 * @property string                           $selfGroupId     Hexadecimal id of the player only group if group is not assigned
 * @property string                           $version         player software version
 * @property string                           $platform_version
 * @property string                           $myIpAddress
 * @property string                           $ip
 * @property string                           $location
 * @property bool                             $playlistOn
 * @property bool                             $cecTvStatus
 * @property bool                             $tvStatus
 * @property bool                             $syncInProgress
 * @property string                           $currentPlaylist
 * @property bool                             $licensed
 * @property string                           $installation
 * @property string                           $lastUpload
 * @property string                           $lastReported
 *
 * @method static Collection all(PiSignageClient $client);
 * @method void toggleTV(array $payload);
 */
class Player extends PiSignageModel {
    protected static array $updatable = [];

    protected static function actions(): array {
        return [
            "all"      => Endpoint::get("/players")->parser(new MultipleResourcesParser(static::class)),
            "toggleTV" => Endpoint::post("/pitv/{_id}")
        ];
    }

    public static function toggleScreen(PiSignageClient $client, $playerId, $state) {
        // status => true to Switch off TV, false to Switch on TV
        (new static($client))->toggleTV(["_id" => $playerId, "status" => !$state]);
    }
}
