<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastJobStatus.php
 */

namespace Neo\Modules\Broadcast\Enums;

enum BroadcastJobStatus: string {
    case Pending = "pending";
    case Active = "active";
    case PendingRetry = "pending-retry";
    case Success = "success";
    case Failed = "failed";
    case Skipped = "skipped";
}
