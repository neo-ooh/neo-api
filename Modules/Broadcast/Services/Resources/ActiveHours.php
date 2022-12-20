<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActiveHours.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

class ActiveHours extends ExternalBroadcasterResource {
    public function __construct(
        /**
         * @var array<array{0: string, 1: string}> List of open-close times pairs in the HH:mm, 24h format,.
         * There should be always be 7 entries in this array. Starting at 0 for Monday, and ending a 6 for sunday.
         */
        public array $days,
    ) {

    }
}
