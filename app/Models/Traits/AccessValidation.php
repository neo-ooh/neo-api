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

use Exception;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * This trait performs access validation at the route-model-binding step.
 * The property `$accessRule` from the model is used to validate that the current user can effectively access the specified resource.
 * The `$accessRule` property MUST contains the name of a Validation Rule class.
 *
 * Trait AccessValidation
 *
 * @property string accessRule The rule used to validate access to the model upon binding it with a route
 *
 * @package Neo\Models\Traits
 */
trait AccessValidation {
    /**
     * Perform access validation before using default mechanism to retrieve the model for a bound value.
     *
     * @param mixed $value
     *
     * @return boolean
     * @throws Exception If the accessRule is not defined
     */
    public function validateAccess($value): bool {
        // Check an access validation method has been defined
        if(empty($this->accessRule)) {
            throw new RuntimeException('$accessRule must be defined when using the AccessValidation trait.');
        }

        // Check access
        return !Validator::make(["model" => $value], ["model" => ["required", new $this->accessRule]])->fails();
    }
}
