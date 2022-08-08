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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\Campaign;

/**
 * Trait HasCampaigns
 *
 * @package Neo\Models\Traits
 *
 * @property Collection<Campaign> $own_campaigns
 * @property Collection<Campaign> $shared_campaigns
 * @property Collection<Campaign> $group_campaigns
 * @property Collection<Campaign> $children_campaigns
 * @property Collection<Campaign> $campaigns
 */
trait HasCampaigns {

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Returns all the campaigns directly owned by this entity
     *
     * @return HasMany
     */
    public function own_campaigns(): HasMany {
        return $this->hasMany(Campaign::class, "owner_id");
    }

    /**
     * Returns all the campaigns shared with this entity
     */
    public function shared_campaigns(): BelongsToMany {
        return $this->belongsToMany(Campaign::class, "campaign_shares", "actor_id", "campaign_id");
    }


    /*
    |--------------------------------------------------------------------------
    | Custom Attributes
    |--------------------------------------------------------------------------
    */

    public function getCampaigns($own = true, $shared = true, $children = true, $parent = true): Collection {
        $campaigns = new Collection();

        // Actor's own campaigns
        if ($own) {
            $campaigns = $campaigns->merge($this->own_campaigns);
        }

        // Campaigns shared with the actor
        if ($shared) {
            $campaigns = $campaigns->merge($this->shared_campaigns);
            $campaigns = $campaigns->merge($this->sharers->flatMap(fn(/** @var Actor $sharer */ $sharer) => $sharer->getCampaigns(true, false, false, false)));
        }

        // Actor's children's campaigns
        if ($children) {
            $campaigns = $campaigns->merge($this->children_campaigns);
        }

        // Campaigns of the parent of the user, if applicable
        if ($parent && !$this->limited_access && ($this->details->parent_is_group ?? false) && !$this->is_group) {
            $campaigns = $campaigns->merge($this->parent->getCampaigns(true, true, true, false));
        }

        return $campaigns->unique("id")->values();
    }

    /**
     * List all the campaigns owned by this entities children
     *
     * @return \Illuminate\Support\Collection
     */
    public function getChildrenCampaignsAttribute(): \Illuminate\Support\Collection {
        $descendants = $this->getAccessibleActors(true, false, false, false)->pluck('id');
        return Campaign::query()->whereIn("owner_id", $descendants)->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Mechanisms
    |--------------------------------------------------------------------------
    */

    /**
     * @param int $campaignId
     *
     * @return bool
     */
    public function canAccessCampaign(int $campaignId): bool {
        return $this->getCampaigns()->pluck('id')->contains($campaignId);
    }
}
