<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScheduleGroup.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign;

enum ScheduleGroup: int {
    case Channel = 1;
    case LoopSlot = 2;
    case Filler = 3;
    case Program = 4;
}
