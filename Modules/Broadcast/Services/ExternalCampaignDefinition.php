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
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class ExternalCampaignDefinition extends Data {
    public function __construct(
        public int            $campaign_id,
        public int            $network_id,
        public int            $format_id,

        /**
         * @var DataCollection<ExternalBroadcasterResourceId>
         */
        #[DataCollectionOf(ExternalBroadcasterResourceId::class)]
        public DataCollection $locations,
    ) {
    }
}
