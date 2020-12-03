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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Neo\Models\Actor;
use Neo\Models\ActorClosure;

/**
 * Trait HasHierarchy
 *
 * @propertu Actor parent
 *
 * @property int               parents_count
 * @property Collection<Actor> parents
 *
 * @property int               children_count
 * @property Collection<Actor> children
 *
 * @method Builder Parents() scope
 * @method Builder Parent() scope
 * @method Builder Children() scope
 *
 * @package  Neo\Models\Traits
 *
 * @mixin Model
 * @mixin QueryBuilder
 */
trait HasHierarchy {
    use WithRelationCaching;

    /**
     * @var int|null Used on an actor creation, this property hold the ID of the parent of the user
     */
    public ?int $actor_parent_id;

    /**
     * Bind to specific model events
     */
    public static function bootHasHierarchy (): void {
        static::created(function (Actor $node) {
            $parent_id = $node->actor_parent_id ?? null;

            // Here we handle the user integration into the global hierarchy.
            // We use its parent closure to build this user additional closures, and give one for itself
            ActorClosure::query()->insertUsing([ "ancestor_id", "descendant_id", "depth" ],
                function (QueryBuilder $query) use ($node, $parent_id) {
                    $query->selectRaw("ancestor_id, {$node->getKey()} as d, depth + 1")
                          ->from("actors_closures")
                          ->where('descendant_id', '=', $parent_id)
                          ->union(function (QueryBuilder $query) use ($node) {
                              $query->selectRaw("{$node->getKey()}, {$node->getKey()}, 0");
                          });
                });
            // We are good
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Table Structure Getters
    |--------------------------------------------------------------------------
    */

    /**
     * Gives the qualified column name on the closure table
     *
     * @param string $column
     *
     * @return string Qualified name of the column "<table>.<column>"
     */
    public function getQualifiedClosureColumn (string $column): string {
        return "actors_closures." . $column;
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to the model's column and the depth column of the closure table.
     * The higher the depth, the farther away the two models are from each other
     *
     * @return Builder
     */
    public function selectActors (): Builder {
        return $this->newQuery()->select([
                        'actors.*',
                        $this->getQualifiedClosureColumn('depth'),
                    ]);
    }

    /**
     * Scope to get all the parent nodes of the current one
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeParents (Builder $query): Builder {
        $ancestorColumn = $this->getQualifiedClosureColumn("ancestor_id");
        $descendantColumn = $this->getQualifiedClosureColumn("descendant_id");

        return $query->join("actors_closures", $ancestorColumn, "=", $this->getQualifiedKeyName())
                     ->where($descendantColumn, "=", $this->getKey())
                     ->where($ancestorColumn, "<>", $this->getKey())
                     ->orderBy('depth');
    }

    /**
     * List all the parents of the model. Each model will have a depth property specifying its relative distance from
     * each other
     *
     * @return Collection
     */
    public function getParentsAttribute (): Collection {
        return $this->getCachedRelation("parents",
            fn () => $this->selectActors()
                          ->parents()
                          ->get()
        );
    }

    /**
     * @return int Number of parents in the hierarchy of this model
     */
    public function getParentsCountAttribute (): int {
        return $this->Parents()->count();
    }

    /**
     * Scope to get the parent node of this model.
     *
     * @param Builder $query
     *
     * @return Builder
     * @see HasHierarchy::scopeParents()
     *
     */
    public function scopeParent (Builder $query): Builder {
        return $query->join("actors_closures",
            $this->getQualifiedClosureColumn("ancestor_id"),
            "=",
            $this->getQualifiedKeyName())
                     ->where($this->getQualifiedClosureColumn("descendant_id"), "=", $this->getKey())
                     ->where($this->getQualifiedClosureColumn('depth'), "=", 1);
    }

    /**
     * The direct parent of the model, null if no parent is set
     *
     * @return Actor|null
     */
    public function getParentAttribute (): ?Actor {
        return $this->getCachedRelation("parent",
            fn () => $this->selectActors()
                          ->Parent()
                          ->first()
        );
    }

    /**~
     * Gets children nodes of the current one
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeChildren (Builder $query): Builder {
        $ancestorColumn = $this->getQualifiedClosureColumn('ancestor_id');
        $descendantColumn = $this->getQualifiedClosureColumn('descendant_id');

        return $query->join("actors_closures", $descendantColumn, "=", $this->getQualifiedKeyName())
                     ->where($ancestorColumn, "=", $this->getKey())
                     ->where($descendantColumn, "<>", $this->getKey())
                     ->orderBy('depth');
    }

    public function getChildrenAttribute () {
        return $this->getCachedRelation("children",
            fn () => $this->selectActors()
                          ->children()
                          ->get());
    }

    public function getChildrenCountAttribute (): int {
        return $this->Children()->count();
    }

    /**
     * Gets children nodes of the current one
     *
     * @return Builder
     */
    public function scopeDirectChildren (): Builder {
        return $this->Children()
                    ->where($this->getQualifiedClosureColumn("depth"), "=", "1");
    }

    public function getDirectChildrenAttribute () {
        return $this->getCachedRelation("direct_children", fn () => $this->selectActors()->directChildren()->get());
    }


    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    /**
     * Change the actor's parent to the given one. Children of the actor follow him, keeping relations unchanged.
     *
     * @param Actor $parent
     *
     * @return HasHierarchy
     */
    public function moveTo (Actor $parent): self {
        if ($parent === null || $this->is($parent)) {
            return $this;
        }

        // We start by unbinding the relationship of this actor and its tree
        DB::delete("DELETE
                        `a`
                    FROM
                        `actors_closures` AS `a`
                    JOIN `actors_closures` AS `b`
                    ON
                        `a`.`descendant_id` = `b`.`descendant_id`
                    LEFT JOIN `actors_closures` AS `c`
                    ON
                        `c`.`ancestor_id` = `b`.`ancestor_id` AND `c`.`descendant_id` = `a`.`ancestor_id`
                    WHERE
                        `b`.`ancestor_id` = ? AND `c`.`ancestor_id` IS NULL",
            [
                $this->getKey(),
            ]);

        // And we rebuild the relationship with the tree in its new position
        DB::insert("INSERT INTO `actors_closures` (`ancestor_id`, `descendant_id`, `depth`)
                    SELECT `supertree`.`ancestor_id`, `subtree`.`descendant_id`,
                    `supertree`.`depth` + `subtree`.`depth` + 1
                    FROM `actors_closures` AS `supertree` JOIN `actors_closures` AS `subtree`
                    WHERE `subtree`.`ancestor_id` = ?
                    AND `supertree`.`descendant_id` = ?;",
            [
                $this->getKey(),
                $parent->getKey(),
            ]);

        $this->parent_is_group = $parent->is_group;
        $this->parent_id = $parent->id;
        $this->setRelation("parent", $parent);

        return $this;
    }


    /*
    |--------------------------------------------------------------------------
    | Queries
    |--------------------------------------------------------------------------
    */

    /**
     * Tell if the current item is a parent at any level of the given item
     *
     * @param Actor $node
     *
     * @return boolean
     */
    public function isParentOf (Actor $node): bool {
        $ancestorColumn = $this->getQualifiedClosureColumn('ancestor_id');
        $descendantColumn = $this->getQualifiedClosureColumn('descendant_id');

        // Count is either 1 if the current actor is a parent, or false otherwise
        $count = DB::table("actors_closures")
                   ->where($ancestorColumn, "=", $this->getKey())
                   ->where($descendantColumn, "=", $node->getKey())
                   ->count('depth');

        return filter_var($count, FILTER_VALIDATE_BOOLEAN);
    }
}
