<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WithPublicRelations.php
 */

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Request;
use InvalidArgumentException;
use RuntimeException;

/**
 * This trait allow for easier expansion of relation on API response.
 * Expandable relations must be defined either with the use of the `publicRelations` property on the model, or a
 * `getPublicRelations()` method. Both the property and the method must return an associative array that map string keys to
 * relations to load.
 *The relation specification allows different formats:
 * ```
 *      "foo" // Load the `foo` relation,
 *      "load:foo" // Load the `foo` relation,
 *      "append:bar" // Append the `bar` attribute,
 *      fn(Model $m) => ... // Execute the closure, passing the model as argument
 *      as attribute.
 *      ["bar", "foo"] // loads the provided relations, all other syntax are also accepted in the array
 * ```
 *
 * @package Neo\Models\Traits
 * @mixin Eloquent
 */
trait WithPublicRelations {
    /**
     * Load public relations, either using the list of relations passed as argument, or by using the current request if
     * no argument is given.
     *
     * @param null $requestedRelations
     * @return self
     */
    public function withPublicRelations($requestedRelations = null): self {
        $publicRelations = $this->getPublicRelationsList();

        foreach ($this->prepareRelationsList($requestedRelations) as $requestedRelation) {
            if (!array_key_exists($requestedRelation, $publicRelations)) {
                // Ignore invalid relations in dev
                if (config("app.env") === 'development') {
                    throw new InvalidARgumentException("Relation '$requestedRelation' is not marked as public for the model '" . static::class . "'");
                }

                continue;
            }

            $this->performExpansion($publicRelations[$requestedRelation]);
        }

        return $this;
    }

    /**
     * Gives the list of public relations for the current model. If no list is defined, an error is thrown.
     *
     * @return array<string, string|callable(static): void>
     */
    public function getPublicRelationsList(): array {
        if (method_exists($this, "getPublicRelations")) {
            $providedRelations = $this->getPublicRelations();
        } else if (property_exists($this, "publicRelations")) {
            $providedRelations = $this->publicRelations;
        } else {
            throw new RuntimeException("Missing property `\$publicRelations` or method `getPublicRelations()` to use the `WithPublicRelations` trait.");
        }

        $publicRelations = [];

        // The public relations list can have a mix of simple value and associative values.
        // We need to normalize the array to an associative list
        foreach ($providedRelations as $key => $value) {
            if (is_string($key)) {
                $publicRelations[$key] = $value;
                continue;
            }

            $publicRelations[$value] = $value;
        }

        return $publicRelations;
    }

    protected function prepareRelationsList($relations) {
        if ($relations) {
            return is_array($relations) ? $relations : [$relations];
        }

        return Request::input("with", []);
    }

    protected function performExpansion(string|array|callable $relation) {
        if (is_array($relation)) {
            foreach ($relation as $value) {
                $this->performExpansion($value);
            }

            return;
        }

        if (is_callable($relation)) {
            $relation($this);
        }

        // Relation is string
        $tokens = explode(":", $relation);
        // If only one token is found, we imply no action is given, and default to `load`
        if (count($tokens) === 1) {
            $action  = "load";
            $request = $tokens[0];
        } else {
            // Otherwise, we validate the given action, and if it is not recognized, we default to `load` as well
            switch ($tokens[0]) {
                case "load":
                case "append":
                    $action  = array_shift($tokens);
                    $request = implode(":", $tokens);
                    break;
                default:
                    $action  = "load";
                    $request = implode(":", $tokens);
            }
        }

        switch ($action) {
            case 'append':
                $this->append($request);
                break;
            case 'load':
                $this->load($request);
                break;
        }
    }
}
