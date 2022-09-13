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

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Models\Location;

class ExternalCampaignDefinition {
    /**
     * @param int                  $campaign_id
     * @param int                  $network_id
     * @param int                  $format_id
     * @param Collection<Location> $locations
     */
    public function __construct(
        public int        $campaign_id,
        public int        $network_id,
        public int        $format_id,
        public Collection $locations,
    ) {
    }
}
