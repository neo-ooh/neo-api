<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterReporting.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Services\Resources\CampaignPerformance;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

interface BroadcasterReporting {
    /**
     * @param ExternalBroadcasterResourceId|array<ExternalBroadcasterResourceId> $campaignIds
     * @return array<CampaignPerformance>
     */
    public function getCampaignsPerformances(ExternalBroadcasterResourceId|array $campaignIds): array;
}
