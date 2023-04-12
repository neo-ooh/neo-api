<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryJobType.php
 */

namespace Neo\Modules\Properties\Jobs;

enum InventoryJobType: string {
    case Pull = "pull";
    case Push = "push";

    case Import = "import";
    case Create = "create";
    case Destroy = "destroy";
}
