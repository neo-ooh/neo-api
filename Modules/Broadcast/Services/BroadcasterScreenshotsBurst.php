<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterScreenshotsBurst.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

interface BroadcasterScreenshotsBurst {
    /**
     * @param array<ExternalBroadcasterResourceId> $players
     * @param string                               $responseUri
     * @param int                                  $scale
     * @param int                                  $duration_ms
     * @param int                                  $frequency_ms
     * @return bool
     */
    public function requestScreenshotsBurst(array $players, string $responseUri, int $scale, int $duration_ms, int $frequency_ms): bool;
}
