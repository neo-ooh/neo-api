<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Format.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;
use Neo\Services\Broadcast\BroadSign\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\Parsers\SingleResourcesParser;

/**
 * Class Support
 * @package Neo\BroadSign\Models
 *
 * @property bool   active
 * @property int    container_id
 * @property int    domain_id
 * @property bool   enforce_orientation
 * @property bool   enforce_resolution
 * @property int    id
 * @property string name
 * @property int    orientation
 * @property int    res_height
 * @property int    res_width
 *
 * @method static Format[] all(BroadsignClient $client)
 *
 */
class Format extends BroadSignModel {

    protected static string $unwrapKey = "display_unit_type";

    protected static function actions(): array {
        return [
            "all" => Endpoint::get("/display_unit_type/v6")
                             ->unwrap(static::$unwrapKey)
                             ->parser(new MultipleResourcesParser(static::class)),
            "get" => Endpoint::get("/display_unit_type/v6/{id}")
                             ->unwrap(static::$unwrapKey)
                             ->parser(new SingleResourcesParser(static::class)),
        ];
    }

    public function frames(): array {
        $locations = collect(Location::all());
        /** @var Location $location */
        $locations = $locations->filter(fn($loc) => $loc->display_unit_type_id === $this->id);
        if($locations->count() === 0) {
            return [];
        }

        return $locations[0]->frames();
    }
}
