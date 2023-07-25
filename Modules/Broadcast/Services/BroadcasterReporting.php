<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterReporting.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Services\Resources\CampaignLocationPerformance;
use Neo\Modules\Broadcast\Services\Resources\CampaignPerformance;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

interface BroadcasterReporting {
    /**
     * @param ExternalBroadcasterResourceId[] $campaignIds
     * @return CampaignPerformance[]
     */
    public function getCampaignsPerformances(array $campaignIds): array;

    /**
     * @param ExternalBroadcasterResourceId[] $campaignIds
     * @return CampaignLocationPerformance[]
     */
    public function getCampaignsPerformancesByLocations(array $campaignIds): array;
}
