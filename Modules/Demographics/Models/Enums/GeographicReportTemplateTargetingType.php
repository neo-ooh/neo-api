<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeographicReportTemplateTargetingType.php
 */

namespace Neo\Modules\Demographics\Models\Enums;

enum GeographicReportTemplateTargetingType: string {
    case all = "all";
    
    case Network = "network";
    case Market = "market";
    case city = "city";
    case tag = "tag";
    case property = "property";
}
