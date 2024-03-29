<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Skin.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign\Models;

use Illuminate\Database\Eloquent\Collection;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\ResourceIDParser;
use Neo\Modules\Broadcast\Services\BroadSign\API\Parsers\SingleResourcesParser;
use Neo\Modules\Broadcast\Services\Resources\Frame;
use Neo\Services\API\Parsers\MultipleResourcesParser;

/**
 * Class Support
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   $active
 * @property int    $domain_id
 * @property int    $geometry_type 1: percent of screen / 2: pixels
 * @property int    $height
 * @property int    $id
 * @property int    $interactivity_timeout
 * @property int    $interactivity_trigger_id
 * @property int    $loop_policy_id
 * @property string $name
 * @property int    $parent_id     Day Part
 * @property int    $screen_no
 * @property int    $width
 * @property int    $x
 * @property int    $y
 * @property int    $z
 *
 * @method static Collection all(BroadSignClient $client)
 * @method static Collection get(BroadSignClient $client, int $frameID)
 * @method static Collection byReservable(BroadSignClient $client, array $params)
 * @method static Collection byDisplayUnit(BroadSignClient $client, int|array $params)
 */
class Skin extends BroadSignModel {

    protected static string $unwrapKey = "skin";

    protected static array $updatable = [
        "id",
        "active",
        "geometry_type",
        "height",
        "interactivity_timeout",
        "interactivity_trigger_id",
        "loop_policy_id",
        "name",
        "parent_id",
        "screen_no",
        "width",
        "x",
        "y",
        "z",
    ];

    protected static function actions(): array {
        return [
            "all"           => Endpoint::get("/skin/v7")
                                       ->unwrap(static::$unwrapKey)
                                       ->parser(new MultipleResourcesParser(static::class)),
            "create"        => Endpoint::post("/skin/v7/add")
                                       ->unwrap(static::$unwrapKey)
                                       ->parser(new ResourceIDParser()),
            "update"        => Endpoint::put("/skin/v7")
                                       ->unwrap(static::$unwrapKey)
                                       ->parser(new ResourceIDParser()),
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
        return DayPart::get($this->api, $this->parent_id);
    }

    /**
     */
    public function toResource(): Frame {
        return new Frame(
            broadcaster_id: $this->getBroadcasterId(),
            external_id   : $this->getKey(),
            name          : $this->name,
            width         : $this->width,
            height        : $this->height,
        );
    }
}
