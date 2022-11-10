<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExternalCampaignDefinition.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Spatie\DataTransferObject\DataTransferObject;

class ExternalCampaignDefinition extends DataTransferObject {
    public int $campaign_id;
    public int $network_id;
    public int $format_id;

    /**
     * @var ExternalBroadcasterResourceId[]
     */
    public array $locations;
}
