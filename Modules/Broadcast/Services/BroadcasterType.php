<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterType.php
 */

namespace Neo\Modules\Broadcast\Services;

enum BroadcasterType: string {
    case BroadSign = "broadsign";
    case PiSignage = "pisignage";
    case SignageOS = "signageos";
}
