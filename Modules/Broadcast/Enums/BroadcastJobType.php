<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastJobType.php
 */

namespace Neo\Modules\Broadcast\Enums;

enum BroadcastJobType: string {
    case PromoteCampaign = "campaign-promote";
    case DeleteCampaign = "campaign-delete";

    case PromoteSchedule = "schedule-promote";
    case DeleteSchedule = "schedule-delete";

    case ImportCreative = "creative-import";
    case UpdateCreative = "creative-update";
    case DeleteCreative = "creative-delete";
}
