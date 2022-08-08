<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignStatus.php
 */

namespace Neo\Modules\Broadcast\Enums;

enum CampaignStatus: string {
    case Empty = "empty";
    case Pending = "pending";
    case Live = "live";
    case Offline = "offline";
    case Expired = "expired";
}
