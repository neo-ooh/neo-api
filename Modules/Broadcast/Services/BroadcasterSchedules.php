<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterSchedules.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcastResourceId;
use Neo\Modules\Broadcast\Services\Resources\Schedule;

interface BroadcasterSchedules {
    /**
     * @param Schedule $schedule
     * @return ExternalBroadcastResourceId
     */
    public function createSchedule(Schedule $schedule): ExternalBroadcastResourceId;

    /**
     * @param ExternalBroadcastResourceId $externalSchedule
     * @param Schedule                    $schedule
     * @return ExternalBroadcastResourceId
     */
    public function updateSchedule(ExternalBroadcastResourceId $externalSchedule, Schedule $schedule): ExternalBroadcastResourceId;

    /**
     * @param ExternalBroadcastResourceId $externalSchedule
     * @return bool
     */
    public function destroySchedule(ExternalBroadcastResourceId $externalSchedule): bool;
}
