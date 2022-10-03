<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignWarning.php
 */

namespace Neo\Modules\Broadcast\Enums;

enum CampaignWarning: string {
    case NoLocations = "no-locations";
    case MultipleBroadcasters = "multiple-broadcasters";
}
