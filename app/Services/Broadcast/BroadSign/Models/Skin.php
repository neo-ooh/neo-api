<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Skin.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use Illuminate\Database\Eloquent\Collection;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Services\Broadcast\BroadSign\API\Parsers\SingleResourcesParser;

/**
 * Class Support
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   $active
 * @property int    $domain_id
 * @property int    $geometry_id 1: percent of screen / 2: pixels
 * @property int    $height
 * @property int    $id
 * @property int    $interactivity_timeout
 * @property int    $interactivity_trigger_id
 * @property int    $loop_policy_id
 * @property string $name
 * @property int    $parent_id   Day Part
 * @property int    $screen_no
 * @property int    $width
 * @property int    $x
 * @property int    $y
 * @property int    $z
 *
 * @method static Collection all(BroadsignClient $client)
 * @method static Collection get(BroadsignClient $client, int $frameID)
 * @method static Collection byReservable(BroadsignClient $client, array $params)
 * @method static Collection byDisplayUnit(BroadsignClient $client, int|array $params)
 */
class Skin extends BroadSignModel {

    protected static string $unwrapKey = "skin";

    protected static function actions(): array {
        return [
            "all"           => Endpoint::get("/skin/v7")
                                       ->unwrap(static::$unwrapKey)
                                       ->parser(new MultipleResourcesParser(static::class)),
            "get"           => Endpoint::get("/skin/v7/{id}")
                                       ->unwrap(static::$unwrapKey)
                                       ->parser(new SingleResourcesParser(static::class))
                                       ->cache(3600),
            "byReservable"  => Endpoint::get("/skin/v7/by_display_unit?display_unit_id={id}")
                                       ->unwrap(static::$unwrapKey)
                                       ->parser(new MultipleResourcesParser(static::class)),
            "byDisplayUnit" => Endpoint::get("/skin/v7/by_display_unit")
                                       ->unwrap(static::$unwrapKey)
                                       ->parser(new MultipleResourcesParser(static::class)),
        ];
    }

    public function dayPart(): DayPart {
        /** @noinspection PhpParamsInspection */
        return DayPart::get($this->api, $this->parent_id);
    }
}
