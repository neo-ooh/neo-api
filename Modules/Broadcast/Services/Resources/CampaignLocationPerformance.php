<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignLocationPerformance.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

class CampaignLocationPerformance extends ExternalBroadcasterResource {
    public function __construct(
        public ExternalBroadcasterResourceId $campaign,

        public ExternalBroadcasterResourceId $location,

        /**
         * @var int How many repetitions for the campaign at this location
         */
        public int                           $repetitions,

        /**
         * @var int How many impressions for the campaign at this location
         */
        public int                           $impressions,
    ) {

    }
}
