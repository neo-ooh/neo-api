<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExternalResourceType.php
 */

namespace Neo\Modules\Broadcast\Enums;

enum ExternalResourceType: string {
    // Default
    case Creative = "creative";
    case Schedule = "schedule";
    case Campaign = "campaign";

    // BroadSign Specific
    case Bundle = "bundle";

    // Others
    case Tag = "tag";
}
