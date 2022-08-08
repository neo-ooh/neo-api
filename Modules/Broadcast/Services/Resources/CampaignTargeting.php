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

class CampaignTargeting extends ExternalBroadcasterResource {
    /**
     * @var array<Tag>
     */
    public array $campaignTags;


    /**
     * @var array<ExternalBroadcasterResourceId>
     */
    public array $locations;

    /**
     * @var array<Tag>
     */
    #[DoNotCompare]
    public array $locationsTags;

    /**
     * @return array<string>
     */
    public function getLocationsExternalIds(): array {
        return array_map(static fn(ExternalBroadcasterResourceId $location) => $location->external_id, $this->locations);
    }

    /**
     * @return array<string>
     */
    public function getLocationsTagsExternalIds(): array {
        return array_map(static fn(Tag $tag) => $tag->external_id, $this->locationsTags);
    }
}
