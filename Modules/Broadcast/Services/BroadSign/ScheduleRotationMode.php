<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScheduleRotationMode.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign;

enum ScheduleRotationMode: int {
    case Ordered = 0;
    case Random = 1;
}
