<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - HasPublicRelations.php
 */

namespace Neo\Models\Traits;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Neo\Helpers\PublicRelations;
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
 *      fn(Models $m) => ... // Execute the closure, passing the model as argument
 *      as attribute.
 *      ["bar", "foo"] // loads the provided relations, all other syntax are also accepted in the array
 * ```
 *
 * @package Neo\Models\Traits
 * @mixin Eloquent
 */
trait HasPublicRelations {
    /**
     * Load public relations, either using the list of relations passed as argument, or by using the current request if
     * no argument is given.
     *
     * @param null $requestedRelations
     * @return self
     */
    public function loadPublicRelations($requestedRelations = null): self {
        PublicRelations::loadPublicRelations($this, $requestedRelations);

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
            throw new RuntimeException("Missing property `\$publicRelations` or method `getPublicRelations()` to use the `HasPublicRelations` trait.");
        }

        $publicRelations = [];

        // The public relations list can have a mix of simple value and associative values.
        // We need to normalize the array to an associative list
        /** @var mixed $value */
        foreach ($providedRelations as $key => $value) {
            if (is_string($key)) {
                $publicRelations[$key] = $value;
                continue;
            }

            if (is_callable($value)) {
                throw new RuntimeException("Public relations with callable must use explicit keys");
            }

            $publicRelations[$value] = $value;
        }

        return $publicRelations;
    }
}
