<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Relation.php
 */

namespace Neo\Helpers;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

function getAsArray(mixed $item) {
    return is_array($item)
        ? $item
        : ($item instanceof Collection
            ? $item->all()
            : [$item]
        );
}

/**
 * Define a relation of a model that can be loaded on demand using the `publicRelations` mechanism
 */
class Relation {
    /**
     * @param string|string[]|null                 $load   The relations to load on the model
     * @param string|string[]|null                 $count  The relations to count on the model
     * @param string|string[]|null                 $append The attribute to
     * @param Capability|Capability[]|Closure|null $gate   If the gate is a function, it should return the
     */
    public function __construct(
        protected string|array|null                  $load = null,
        protected string|array|null                  $count = null,
        protected string|array|null                  $append = null,
        protected Closure|null                       $custom = null,
        protected Capability|Closure|array|bool|null $gate = null,
    ) {
    }

    public static function make(
        string|array|null                  $load = null,
        string|array|null                  $count = null,
        string|array|null                  $append = null,
        Closure|null                       $custom = null,
        Capability|Closure|array|bool|null $gate = null,
    ) {
        return new static($load, $count, $append, $custom, $gate);
    }

    public static function fromLegacy(string|callable $expansion): Relation {
        if (is_callable($expansion)) {
            return new static(custom: $expansion);
        }

        // The expansion is a string, we need to pars it
        $tokens = explode(":", $expansion);
        // If only one token is found, we imply no action is given, and default to `load`
        if (count($tokens) === 1) {
            return new static(load: $tokens[0]);
        } else {
            if (in_array($tokens[0], ["load", "append", "count"])) {
                $action = array_shift($tokens);
            } else {
                $action = "load";
            }

            $argument = implode(':', $tokens);

            // Otherwise, we validate the given action, and if it is not recognized, we default to `load` as well
            return match ($action) {
                "append" => new static(append: $argument),
                "count"  => new static(count: $argument),
                default  => new static(load: $argument),
            };
        }
    }

    /**
     * Perform the proper resource expansion on the model.
     * If a gate is provided, it will be checked before any action is done.
     *
     * @param Model|Collection $subject
     * @return bool True if the expansion was done, false if it was blocked by the gate
     */
    public function expand(Model|Collection $subject): bool {
        // First, check we actually have something to work with.
        // If the collection is empty, we end early.
        if ($subject instanceof Collection && $subject->isEmpty()) {
            return true;
        }

        // Validate the relation can be loaded
        if (!$this->isGateOpen($subject)) {
            return false;
        }

        if ($this->load !== null) {
            $subject->loadMissing($this->load);
        }

        if ($this->count !== null) {
            $subject->loadCount($this->count);
        }

        if ($this->append !== null) {
            $subject->append($this->append);
        }

        if ($this->custom !== null) {
            $customExpansion = $this->custom;
            foreach (getAsArray($subject) as $subject) {
                $customExpansion($subject);
            }
        }

        return true;
    }

    protected function isGateOpen(Model|Collection $subject): bool {
        // Is there a closure ?
        if ($this->gate === null) {
            return true;
        }

        if (is_bool($this->gate)) {
            return $this->gate;
        }

        // Closure is a Capability, use the `Gate` facade to validate it
        if ($this->gate instanceof Capability) {
            return Gate::allows($this->gate->value);
        }

        if (is_array($this->gate)) {
            return collect($this->gate)
                ->filter(fn(Capability $capability) => Gate::allows($capability->value))
                ->isNotEmpty();
        }

        // Gate is a closure, call it with a single model
        $excerpt = $subject instanceof Collection ? $subject->first() : $subject;

        // We have to move the gate closure from the class property to a variable.
        // Otherwise, calling it with `$this->gate(...)` would resut in PHP trying to call a
        // `gate` method on this class.
        $gate = $this->gate;
        return $gate($excerpt);
    }
}
