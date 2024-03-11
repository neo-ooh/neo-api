<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReportStatus.php
 */

namespace Neo\Modules\Demographics\Models\Enums;

enum ReportStatus: string {
    case Pending = "PENDING";
    case Active = "ACTIVE";
    case Done = "DONE";
    case Failed = "FAILED";
}
