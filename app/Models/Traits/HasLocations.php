<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
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

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Returns all the locations directly associated to this entity
     *
     * @return BelongsToMany
     */
    public function own_locations (): BelongsToMany {
        return $this->belongsToMany(Location::class, "actors_locations", "actor_id", "location_id");
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Attributes
    |--------------------------------------------------------------------------
    */

    /**
     * List all the locations of the parent group for this entity. If this entity's parent is not a group, returns an
     * empty collection
     *
     * @return Collection<Location>
     */
    public function getGroupLocationsAttribute (): Collection {
        if ($this->parent_id === null || !$this->parent->is_group) {
            return new Collection();
        }

        $group = $this->parent;
        $locations = new Collection();
        $locations->push(...$group->own_locations);
        $locations->push(...$group->group_locations);
        return $locations;
    }

    /**
     * List ALL locations this user has access to
     *
     * @return Collection<Location>
     */
    public function getLocationsAttribute (): Collection {
        $locations = new Collection();
        $locations->push(...$this->own_locations);
        $locations->push(...$this->group_locations);
        return $locations->unique();
    }

    /*
    |--------------------------------------------------------------------------
    | Custom Mechanisms
    |--------------------------------------------------------------------------
    */

    public function canAccessLocation (Location $location): bool {
        return $this->locations->pluck('id')->contains($location->id);
    }
}
