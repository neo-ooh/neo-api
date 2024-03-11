<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DatasetStructure.php
 */

namespace Neo\Modules\Demographics\Models\Enums;

enum DatasetStructure: string {
    case Flat = "FLAT";
    case Hierarchy = "HIERARCHY";
}
