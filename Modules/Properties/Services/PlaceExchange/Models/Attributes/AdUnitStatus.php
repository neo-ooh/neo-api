<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdUnitStatus.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes;

enum AdUnitStatus: int {
    case Pending = 1;
    case OnDemand = 2;
    case Live = 3;
    case Inactive = 4;
    case Decommissioned = 5;
}
