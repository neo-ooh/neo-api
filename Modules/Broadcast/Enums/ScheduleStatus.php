<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScheduleStatus.php
 */

namespace Neo\Modules\Broadcast\Enums;

enum ScheduleStatus: string {
    case Draft = "draft";
    case Pending = "pending";
    case Approved = "approved";
    case Live = "live";
    case Rejected = "rejected";
    case Expired = "expired";
    case Trashed = "trashed";
}
