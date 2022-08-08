<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScheduleDetails.php
 */

namespace Neo\Modules\Broadcast\Models;

use Neo\Models\DBView;

/**
 * @property-read int $schedule_id
 * @property-read int $is_approved
 */
class ScheduleDetails extends DBView {
    protected $table = "schedule_details";
}
