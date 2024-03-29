<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessibleContent.php
 */

namespace Neo\Modules\Broadcast\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Neo\Modules\Broadcast\Models\Content;

class AccessibleContent implements Rule {
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool {
        $content = Content::with("library")->findOrFail($value);
        return $content->library->isAccessibleBy(Auth::user());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string {
        return 'The validation error message.';
    }
}
