<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScheduleContentDisabledFormat.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

/**
 * @property int $schedule_content_id
 * @property int $format_id
 */
class ScheduleContentDisabledFormat extends Model {
    use AsPivot;

    protected $table = "schedule_content_disabled_formats";
}
