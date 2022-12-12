<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HasCampaigns.php
 */

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Modules\Broadcast\Models\Campaign;

/**
 * Trait HasCampaigns
 *
 * @package Neo\Models\Traits
 *
 * @property Collection<Campaign> $campaigns
 */
trait HasCampaigns {
    /**
     * Returns all the campaigns directly owned by this entity
     *
     * @return HasMany
     */
    public function campaigns(): HasMany {
        return $this->hasMany(Campaign::class, "parent_id");
    }

    /**
     * @param int $campaignId
     *
     * @return bool
     */
    public function canAccessCampaign(int $campaignId): bool {
        return $this->getAccessibleActors(ids: true)->contains(Campaign::find($campaignId)->parent_id);
    }
}
