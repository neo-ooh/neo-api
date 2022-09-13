<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessibleCreative.php
 */

namespace Neo\Modules\Broadcast\Rules;

use Illuminate\Contracts\Validation\Rule;

class AccessibleCreative implements Rule {
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool {
        // Dummy check as creative are always accessed with their content, which has the validation logic
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string {
        return '';
    }
}
