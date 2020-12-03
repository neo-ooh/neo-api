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

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait WithRelationCaching
 *
 * @package Neo\Models\Traits
 *
 * @mixin Model
 */
trait WithRelationCaching {
    /**
     * Returns the specified relation. If the relation is not set and a value is provided, the value will be executed
     * and its result will be stored then returned. The value takes the form of a closure as to prevent executing it
     * everytime the method is called.
     *
     * @param string   $relation Name of the relation
     * @param ?Closure $value
     *
     * @return mixed|null
     */
    protected function getCachedRelation (string $relation, ?Closure $value = null) {
        // Check if the relation is already loaded
        if (!$this->relationLoaded($relation)) {
            if (is_null($value)) {
                // The relation is not loaded, and no value is provided, we return null
                return null;
            }

            // Store the closure result
            $this->setRelation($relation, $value());
        }

        return $this->getRelation($relation);
    }
}
