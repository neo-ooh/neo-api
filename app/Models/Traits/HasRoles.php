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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Neo\Enums\Capability as CapabilityEnum;
use Neo\Models\Actor;
use Neo\Models\ActorCapability;
use Neo\Models\ActorRole;
use Neo\Models\Capability;
use Neo\Models\Role;

/**
 * Trait HasRoles
 *
 * @package Neo\Models\Traits
 *
 * @property Collection<Capability> $capabilities                       List all capabilities directly and indirectly
 *           applying to this user
 * @property Collection<Capability> $standalone_capabilities            List all capabilities directly and indirectly
 *           applying to this user
 * @property Collection<Role>       $roles                              List all roles directly and indirectly applying
 *           to this user
 * @property Collection<Role>       $own_roles                          Roles directly applying to this actor
 * @property Collection<Role>       $inherited_roles                    Roles applying to the parent of this actor, IF
 *           the parent is
 *
 * @method Builder Capabilities() scope
 * @method Builder OwnRoles() scope
 * @method Builder InheritedRoles() scope
 *
 */
trait HasRoles {
    use WithRelationCaching;

    /*
    |--------------------------------------------------------------------------
    | Capabilities mechanisms
    |--------------------------------------------------------------------------
    */

    public function addCapability(CapabilityEnum $capability): self {
        $cap = new ActorCapability([
            "actor_id"      => $this->getKey(),
            "capability_id" => Capability::bySlug($capability)->id,
        ]);

        $cap->save();

        // Reload this model capabilities
        $this->reloadCapabilities();

        return $this;
    }

    public function addCapabilities(array $capabilities): self {
        foreach ($capabilities as $capability) {
            ActorCapability::query()->create([
                "actor_id"      => $this->getKey(),
                "capability_id" => $capability,
            ]);
        }

        // Reload this model capabilities
        $this->reloadCapabilities();

        return $this;
    }

    public function revokeCapability(CapabilityEnum $capability): self {
        DB::delete("DELETE FROM `actors_capabilities` WHERE `capability_id` = ? AND `actor_id` = ?",
            [
                Capability::bySlug($capability)->id,
                $this->getKey(),
            ]);

        // Reload this model capabilities
        $this->reloadCapabilities();

        return $this;
    }

    /**
     * @param int[] $roles
     *
     * @return HasRoles
     * @return HasRoles
     */
    public function addRoles(array $roles): self {
        foreach ($roles as $roleID) {
            ActorRole::query()->create([
                "actor_id" => $this->getKey(),
                "role_id"  => $roleID,
            ]);
        }

        return $this;
    }

    /**
     * @param int[] $roles
     *
     * @return HasRoles
     */
    public function removeRoles(array $roles): self {
        $binds = implode(", ", array_fill(0, count($roles), "?"));
        DB::delete("DELETE FROM `actors_roles` WHERE `actor_id` = ? AND `role_id` IN ($binds)",
            [
                $this->getKey(),
                ...$roles
            ]);

        return $this;
    }

    public function syncRoles(array $roles): void {
        // All good, update the roles
        $rolesID = $this->own_roles->pluck('id')->toArray();

        $toAdd    = array_diff($roles, $rolesID);
        $toRemove = array_diff($rolesID, $roles);

        if (count($toAdd) > 0) {
            $this->addRoles($toAdd);
        }

        if (count($toRemove) > 0) {
            $this->removeRoles($toRemove);
        }

        $this->unsetRelation("own_roles");
    }

    protected function reloadCapabilities(): void {
        if ($this->relationLoaded("standalone_capabilities")) {
            $this->unsetRelation("standalone_capabilities");
        }

        if ($this->relationLoaded("capabilities")) {
            $this->unsetRelation("capabilities");
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Actor's Capabilities
    |--------------------------------------------------------------------------
    */

    /**
     * Scope
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeCapabilities(Builder $query): Builder {
        return $query->setModel(new Capability())
                     ->setEagerLoads([])
                     ->select("c.*")
                     ->from("capabilities", "c")
                     ->join("roles_capabilities as rc", "rc.capability_id", "=", "c.id")
                     ->whereIn("rc.role_id", $this->select("r.id")->fromSub($this->Roles(), "r"))
                     ->distinct()
                     ->union($this->OwnStandaloneCapabilities());
    }

    /**
     * List all capabilities applied directly to the user and through its roles
     *
     * @return Collection|null
     */
    public function getCapabilitiesAttribute() {
        return $this->getCachedRelation("capabilities", fn() => $this->Capabilities()->get());
    }

    /*
    |--------------------------------------------------------------------------
    | Actor's Own Standalone Capabilities
    |--------------------------------------------------------------------------
    */

    /**
     * Scope
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeOwnStandaloneCapabilities(Builder $query): Builder {
        return $query->setModel(new Capability())
                     ->setEagerLoads([])
                     ->select("c.*")
                     ->from("capabilities", "c")
                     ->join("actors_capabilities as uc", "c.id", "=", "uc.capability_id")
                     ->where("uc.actor_id", "=", $this->getKey());
    }

    public function getStandaloneCapabilitiesAttribute(): Collection {
        return $this->getCachedRelation("standalone_capabilities",
            fn() => $this->OwnStandaloneCapabilities()->get());
    }


    /*
    |--------------------------------------------------------------------------
    | Actor's Aggregated Roles
    |--------------------------------------------------------------------------
    */
    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeRoles(Builder $query): Builder {
        $roles = $query->OwnRoles();

        if (!$this->is_group && ($this->parent->is_group ?? false)) {
            $roles->union($this->InheritedRoles());
        }

        return $roles;
    }

    /**
     * List ALL the roles of this user
     *
     * @return Collection<Role>
     */
    public function getRolesAttribute(): Collection {
        return $this->getCachedRelation("roles", fn() => $this->Roles()->get());
    }

    /*
    |--------------------------------------------------------------------------
    | Actor's Roles
    |--------------------------------------------------------------------------
    */

    /**
     * Returns all the roles directly applied to this entity
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeOwnRoles(Builder $query): Builder {
        return $query->setModel(new Role())
                     ->setEagerLoads([])
                     ->select("r.*")
                     ->from("roles", "r")
                     ->join("actors_roles AS ur", "ur.role_id", "=", "r.id")
                     ->where("ur.actor_id", "=", $this->getKey());
    }

    public function getOwnRolesAttribute(): Collection {
        return $this->getCachedRelation("own_roles", fn() => $this->OwnRoles()->get());
    }

    /*
    |--------------------------------------------------------------------------
    | Actor's Parent's Roles
    |--------------------------------------------------------------------------
    */
    public function scopeInheritedRoles(Builder $query): Builder {
        // Select all roles of the parent if it's a group
        return $query->setModel(new Role())
                     ->setEagerLoads([])
                     ->when(!$this->is_group, function (Builder $query) {
                         $query->select("r.*")
                               ->from("roles", "r")
                               ->join("actors_roles AS ur", "ur.role_id", "=", "r.id")
                               ->join("actors AS u", "u.id", "=", "ur.actor_id")
                               ->join("actors_closures AS uc", "uc.ancestor_id", "=", "u.id")
                               ->where("uc.descendant_id", "=", $this->getKey())
                               ->where("uc.depth", "=", 1)
                               ->where("u.is_group", "=", true);
                     });

    }

    /**
     * List all the roles of the parent group for this entity. If this entity's parent is not a group, returns an
     * empty collection
     *
     * @return Collection<Role>
     */
    public function getInheritedRolesAttribute(): Collection {
        if (!$this->is_group && ($this->parent->is_group ?? false)) {
            return $this->getCachedRelation("inherited_roles", fn() => $this->InheritedRoles()->get());
        }

        return new Collection();
    }

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
                $reviewers = $actor->getAccessibleActors(true, true, false, false)
                                   ->filter(fn($child) => !$child->is_group && $child->hasCapability($capability))
                                   ->each(fn($actor) => $actor->unsetRelations());

                if ($reviewers->count() > 0) {
                    return $reviewers;
                }
            } else if ($actor->hasCapability(Capability::contents_review())) {
                // This actor has the proper capability, use it
                return collect([$actor]);
            }

            // No match, go up
            $actor = $actor->parent;
        } while ($actor !== null);

        return new Collection();
    }
}
