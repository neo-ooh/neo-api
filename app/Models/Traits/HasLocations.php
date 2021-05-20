<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HasLocations.php
 */

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Neo\Models\Location;

/**
 * Trait HasCampaigns
 *
 * @package NeoModels\Traits
 *
 * @property Collection<Location> own_locations
 * @property Collection<Location> group_locations
 * @property Collection<Location> locations
 */
trait HasLocations {
    public function getLocations($own = true, $group = true, $children = true, $recurs = false): Collection {
        $locations = new Collection();

        if($group && !$this->is_group && $this->details->parent_is_group) {
            $locations = $locations->merge($this->parent->getLocations(true, false, true));
        } else if($own) {
            $locations = $locations->merge($this->own_locations);
        }

        if($children && !$recurs) {
            $locations = $locations->merge($this->direct_children->map(fn($child) => $child->getLocations(true, false, $children && $recurs, $recurs))->flatten());
        }

        if($children && $recurs) {
            $allChildren = $this->children;
            $allChildren->load("own_locations");
            $locations = $locations->merge($allChildren->pluck("own_locations")->flatten());
        }

        return $locations->unique("id")->values();
    }

    public function getLocationsAttribute(): Collection {
        return $this->getLocations();
    }

    /**
     * Returns all the locations directly associated to this entity
     *
     * @return BelongsToMany
     */
    public function own_locations (): BelongsToMany {
        return $this->belongsToMany(Location::class, "actors_locations", "actor_id", "location_id");
    }

    /**
     * List all the locations of the parent group for this entity. If this entity's parent is not a group, returns an
     * empty collection
     *
     * @return Collection<Location>
     */
    public function getGroupLocationsAttribute (): Collection {
        if ($this->is_group || !$this->details->parent_is_group) {
            return new Collection();
        }

        return $this->parent->own_locations;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    public function canAccessLocation (Location $location): bool {
        return $this->getLocations()->contains("id", "=", $location->id);
    }
}
