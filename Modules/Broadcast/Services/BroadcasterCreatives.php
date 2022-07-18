<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterCreatives.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Services\Resources\Creative;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcastResourceId;

interface BroadcasterCreatives {
    /**
     * @param Creative $creative
     * @return ExternalBroadcastResourceId
     */
    public function createCreative(Creative $creative): ExternalBroadcastResourceId;

    /**
     * @param ExternalBroadcastResourceId $externalCreative
     * @return bool
     */
    public function deleteCreative(ExternalBroadcastResourceId $externalCreative): bool;
}
