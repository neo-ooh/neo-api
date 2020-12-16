<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - HasCampaigns.php
 */

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Models\Campaign;

/**
 * Trait HasCampaigns
 *
 * @package Neo\Models\Traits
 *
 * @property Collection<Campaign> own_campaigns
 * @property Collection<Campaign> shared_campaigns
 * @property Collection<Campaign> group_campaigns
 * @property Collection<Campaign> children_campaigns
 * @property Collection<Campaign> campaigns
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
    public function own_campaigns (): HasMany {
        return $this->hasMany(Campaign::class, "owner_id");
    }

    /**
     * Returns all the campaigns shared with this entity
     */
    public function shared_campaigns (): BelongsToMany {
        return $this->belongsToMany(Campaign::class, "campaign_shares", "actor_id", "campaign_id");
    }


    /*
    |--------------------------------------------------------------------------
    | Custom Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * List all the campaigns of the parent group for this entity. If this entity's parent is not a group, returns an
     * empty collection
     *
     * @return Collection<Campaign>
     */
    public function getGroupCampaignsAttribute (): Collection {
        if ($this->parent_id === null || !$this->parent->is_group) {
            return new Collection();
        }

        $group = $this->parent;
        $campaigns = new Collection();
        $campaigns->push(...$group->own_campaigns);
        $campaigns->push(...$group->shared_campaigns);
        $campaigns->push(...$group->group_campaigns);
        return $campaigns;
    }

    /**
     * List all the campaigns owned by this entities children
     *
     * @return \Illuminate\Support\Collection
     */
    public function getChildrenCampaignsAttribute (): \Illuminate\Support\Collection {
        $descendants = $this->accessible_actors->pluck('id');
        return Campaign::whereIn("owner_id", $descendants)->get();
    }

    /**
     * List ALL campaigns this user has access to
     *
     * @return Collection<Campaign>
     */
    public function getCampaignsAttribute (): Collection {
        $campaigns = new Collection();
        $campaigns->push(...$this->own_campaigns);
        $campaigns->push(...$this->shared_campaigns);
        $campaigns->push(...$this->group_campaigns);
        $campaigns->push(...$this->children_campaigns);
        return $campaigns->unique();
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Mechanisms
    |--------------------------------------------------------------------------
    */

    /**
     * @param Campaign $campaign
     *
     * @return bool
     */
    public function canAccessCampaign (Campaign $campaign): bool {
        return $this->campaigns->pluck('id')->contains($campaign->id);
    }
}
