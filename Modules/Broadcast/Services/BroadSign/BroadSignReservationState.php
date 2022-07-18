<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignReservationState.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign;

enum BroadSignReservationState: int {
    case Held = 0;
    case Contracted = 1;
    case Cancelled = 2;
    case HeldCancelled = 3;
    case GoalReached = 4;
}
