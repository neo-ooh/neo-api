<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessibleLocation.php
 */

namespace Neo\Modules\Broadcast\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Location;

class AccessibleLocation implements Rule, ImplicitRule {
    /**
     * Create a new rule instance.
     */
    public function __construct() {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool {
        if (Gate::allows(Capability::locations_edit->value)) {
            return true;
        }

        /** @var Location|null $location */
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
    public function message(): string {
        return 'You do not have access to the specified location';
    }
}
