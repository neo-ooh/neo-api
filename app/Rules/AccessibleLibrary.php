<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - AccessibleLibrary.php
 */

namespace Neo\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Neo\Models\Library;

class AccessibleLibrary implements Rule {
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct () {
        //
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
        if(!is_numeric($value)) {
            return false;
        }

        /** @var Library $library */
        $library = Library::query()->findOrFail($value);
        return $library->isAccessibleBy(Auth::user());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message (): string {
        return 'You do not have access to the specified library';
    }
}
