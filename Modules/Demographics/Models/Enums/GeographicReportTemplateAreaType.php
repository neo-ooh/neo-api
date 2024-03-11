<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeographicReportTemplateAreaType.php
 */

namespace Neo\Modules\Demographics\Models\Enums;

enum GeographicReportTemplateAreaType: string {
    case Radius = "radius";
    case Isochrone = "isochrone";

    case Custom = "custom";
}
