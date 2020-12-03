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

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Request;
use RuntimeException;

/**
 * Trait WithPublicRelations
 *
 * @package Neo\Models\Traits
 * @mixin Eloquent
 */
trait WithPublicRelations {
    /**
     * Load public relations, either using the list of relations passed as argument, or by using the current request if
     * no argument is given.
     *
     * @param string|string[]
     */
    public function withPublicRelations ($relations = null): void {
        $publicRelations = $this->getPublicRelationsList();

        $with = [];

        foreach ($this->prepareRelationsList($relations) as $relation) {
            if (in_array($relation, $publicRelations, true)) {
                $with[] = $relation;
            }

            trigger_error("Relation '$relation' is not marked as public for the model '" . static::class . "'",
                E_USER_WARNING);
        }

        $this->load($with);
    }

    /**
     * Gives the list of public relations for the current model. If no list is defined, an error is thrown.
     *
     * @return array
     */
    protected function getPublicRelationsList (): array {
        if (method_exists($this, "getPublicRelations")) {
            return $this->getPublicRelations();
        }

        if (property_exists($this, "publicRelations")) {
            return $this->publicRelations;
        }

        throw new RuntimeException("Model must implement either the property '\$publicRelations' or the method 'getPublicRelations' to use the WithPublicRelations Trait.");
    }

    protected function prepareRelationsList ($relations) {
        if ($relations) {
            return is_array($relations) ? $relations : [ $relations ];
        }

        return Request::input("with", []);
    }
}
