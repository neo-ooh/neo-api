<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterCampaigns.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Services\Resources\Campaign;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcastResourceId;

interface BroadcasterCampaigns {
    public function createCampaign(Campaign $campaign): ExternalBroadcastResourceId;

    public function checkCampaign(ExternalBroadcastResourceId $externalCampaign, Campaign $expected): ResourcesComparator;

    public function updateCampaign(ExternalBroadcastResourceId $externalCampaign, Campaign $campaign): ExternalBroadcastResourceId;

    public function deleteCampaign(ExternalBroadcastResourceId $externalCampaign): bool;
}
