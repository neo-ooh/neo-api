<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignSearchResult.php
 */


namespace Neo\Modules\Broadcast\Services\Resources;

class CampaignSearchResult extends Campaign {
    public function __construct(
        bool                                 $enabled,
        string                               $name,
        string                               $start_date,
        string                               $start_time,
        string                               $end_date,
        string                               $end_time,
        int                                  $broadcast_days,
        int                                  $priority,
        float                                $occurrences_in_loop,
        ExternalBroadcasterResourceId|null   $advertiser,
        public ExternalBroadcasterResourceId $id,
        int                                  $duration_msec = 0
    ) {
        parent::__construct(
            enabled            : $enabled,
            name               : $name,

            start_date         : $start_date,
            start_time         : $start_time,

            end_date           : $end_date,
            end_time           : $end_time,

            broadcast_days     : $broadcast_days,
            priority           : $priority,

            occurrences_in_loop: $occurrences_in_loop,
            advertiser         : $advertiser,

            duration_msec      : $duration_msec,
        );
    }
}
