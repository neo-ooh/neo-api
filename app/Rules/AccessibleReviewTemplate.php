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

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Neo\Models\ReviewTemplate;

class AccessibleReviewTemplate implements Rule {
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
        $template = ReviewTemplate::query()->findOrFail($value);
        return Auth::user()->hasAccessTo($template->owner);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message (): string {
        return 'You do not have access to the specified review template';
    }
}
