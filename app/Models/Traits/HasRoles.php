<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HasRoles.php
 */

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Neo\Enums\Capability as CapabilityEnum;
use Neo\Models\Actor;
use Neo\Models\Capability;
use Neo\Models\Role;
use Neo\Models\Utils\ActorsGetter;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * Trait HasRoles
 *
 * @package Neo\Models\Traits
 *
 * @property Collection<Role>       $roles
 * @property Collection<Capability> $roles_capabilities
 * @property Collection<Capability> $standalone_capabilities
 *
 * @property Collection<Capability> $capabilities Roles and standalone capabilities aggregated
 */
trait HasRoles {
    use WithRelationCaching;
    use HasRelationships;
    use HasCapabilities;

    /*
    |--------------------------------------------------------------------------
    | Capabilities mechanisms
    |--------------------------------------------------------------------------
    */
    /**
     * @param array<int> $roles
     * @return void
     */
    public function syncRoles(array $roles): void {
        $this->roles()->sync($roles);
        $this->reloadCapabilities();
    }

    protected function reloadCapabilities(): void {
        if ($this->relationLoaded("roles")) {
            $this->unsetRelation("roles");
        }
        if ($this->relationLoaded("standalone_capabilities")) {
            $this->unsetRelation("standalone_capabilities");
        }

        if ($this->relationLoaded("roles_capabilities")) {
            $this->unsetRelation("roles_capabilities");
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Actor's Capabilities
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsToMany<Role>
     */
    public function roles() {
        return $this->belongsToMany(Role::class, "actors_roles", "actor_id", "role_id")
                    ->withTimestamps();
    }

    /**
     * @return HasManyDeep<Capability>
     */
    public function roles_capabilities(): HasManyDeep {
        return $this->hasManyDeepFromRelations([$this->roles(), (new Role())->capabilities()]);
    }

    /**
     * @return BelongsToMany<Capability>
     */
    public function standalone_capabilities(): BelongsToMany {
        return $this->belongsToMany(Capability::class, "actors_capabilities", "actor_id", "capability_id");
    }

    public function capabilities() {
        return $this->belongsToMany(Capability::class, "actors_all_capabilities", "actor_id", "capability_id");
    }

    /*
    |--------------------------------------------------------------------------
    | Actor's Parent's Roles
    |--------------------------------------------------------------------------
    */

    /**
     * This returns the user.s in the current group or above that have the specified capability.
     * The method stops searching once it finds at least one actor with the specified capability.
     * If multiple actors in the same group have the capability, they will all be returned.
     *
     * @param CapabilityEnum $capability
     * @return \Illuminate\Support\Collection<Actor> A list of actors with the capability. These actors are guaranteed to have
     *                                               access to the current actor.
     */
    public function getActorsInHierarchyWithCapability(CapabilityEnum $capability) {
        // We start by the current actor and we move upward until we found someone
        $actor = $this;

        do {
            // Is this actor a group ?
            if ($actor->is_group) {
                // Does this group has actor with the proper capability ?
                $reviewers = ActorsGetter::from($actor)
                                         ->selectChildren()
                                         ->getActors()
                                         ->load(["roles_capabilities", "standalone_capabilities"])
                                         ->filter(fn($child) => !$child->is_group && $child->hasCapability($capability));

                if ($reviewers->count() > 0) {
                    return $reviewers;
                }
            } else if ($actor->hasCapability(CapabilityEnum::contents_review)) {
                // This actor has the proper capability, use it
                return collect([$actor]);
            }

            // No match, go up
            $actor = $actor->parent;
        } while ($actor !== null);

        return new Collection();
    }
}
