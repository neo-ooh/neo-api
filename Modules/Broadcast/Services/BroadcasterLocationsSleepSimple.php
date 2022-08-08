<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterLocationsSleepSimple.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

interface BroadcasterLocationsSleepSimple {
    /**
     * Update the sleep schedule of a location
     *
     * @param ExternalBroadcasterResourceId $location
     * @param bool                          $enabled     Is the sleep cycle enabled ?
     * @param string|null                   $sleep_start time string in 24h format (hh:mm:ss)
     * @param string|null                   $sleep_end   time string in 24h format (hh:mm:ss)
     * @return mixed
     */
    public function updateSleepSchedule(ExternalBroadcasterResourceId $location, bool $enabled, string|null $sleep_start, string|null $sleep_end);
}
