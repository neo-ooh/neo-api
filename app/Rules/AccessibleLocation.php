<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\Location;

class AccessibleLocation implements Rule, ImplicitRule {
    /**
     * Create a new rule instance.
     */
    public function __construct () {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes ($attribute, $value): bool {
        if(Gate::allows(Capability::locations_edit)) {
            return true;
        }

        /** @var Location $location */
        $location = Location::query()->find($value);

        if (is_null($location)) {
            return false;
        }

        return Auth::user()->canAccessLocation($location);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message (): string {
        return 'You do not have access to the specified location';
    }
}
