<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPerformance.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

class CampaignPerformance extends ExternalBroadcasterResource {
    public function __construct(
        public ExternalBroadcasterResourceId $campaign,
        /**
         * @var string Date string YYYY-mm-DD
         */
        public string                        $date,

        /**
         * @var int How many repetitions for the campaign on this date
         */
        public int                           $repetitions,

        /**
         * @var int How many impressions for the campaign on this date
         */
        public int                           $impressions,
    ) {

    }
}
