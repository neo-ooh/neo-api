<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SkinSlot.php
 */

namespace Neo\Services\Broadcast\BroadSign\Models;

use Illuminate\Support\Collection;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\API\Parsers\MultipleResourcesParser;
use Neo\Services\Broadcast\BroadSign\API\BroadSignEndpoint as Endpoint;

/**
 * Class LoopPolicy
 *
 * @package Neo\BroadSign\Models
 *
 * @property bool   $active
 * @property string $day_mask
 * @property int    $day_of_week_mask
 * @property int    $domain_id
 * @property string $end_or_deactivated_date
 * @property int    $id
 * @property int    $parent_id
 * @property int    $skin_id
 * @property string $start_date
 * @property bool   $temporary
 *
 *
 * @method static Collection forCampaign(BroadsignClient $client, array $parameters)
 */
class SkinSlot extends BroadSignModel {

    protected static string $unwrapKey = "skin_slot";

    protected static function actions(): array {
        return [
            "forCampaign" => Endpoint::get("/skin_slot/v7/by_reservable")
                                     ->unwrap(static::$unwrapKey)
                                     ->parser(new MultipleResourcesParser(static::class)),
        ];
    }
}
