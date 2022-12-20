<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignTargeting.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

use Neo\Modules\Broadcast\Services\DoNotCompare;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

class CampaignTargeting extends ExternalBroadcasterResource {
    public function __construct(
        /**
         * @var DataCollection<Tag>
         */
        #[DataCollectionOf(Tag::class)]
        public DataCollection $campaignTags,

        /**
         * @var DataCollection<ExternalBroadcasterResourceId>
         */
        #[DataCollectionOf(ExternalBroadcasterResourceId::class)]
        public DataCollection $locations,

        /**
         * @var DataCollection<Tag>
         */
        #[DataCollectionOf(Tag::class)]
        #[DoNotCompare]
        public DataCollection $locationsTags,
    ) {
    }

    /**
     * @return array<string>
     */
    public function getLocationsExternalIds(): array {
        return array_map(static fn(ExternalBroadcasterResourceId $location) => $location->external_id, $this->locations->items());
    }

    /**
     * @return array<string>
     */
    public function getLocationsTagsExternalIds(): array {
        return array_map(static fn(Tag $tag) => $tag->external_id, $this->locationsTags->items());
    }
}
