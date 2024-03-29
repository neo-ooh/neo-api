<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessibleActor.php
 */

namespace Neo\Rules;

use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Neo\Models\AccessToken;
use Neo\Models\Actor;

class AccessibleActor implements Rule, ImplicitRule {
    protected $allow_self;

    /**
     * Create a new rule instance.
     *
     * @param bool $allow_self
     */
    public function __construct(bool $allow_self = true) {
        $this->allow_self = $allow_self;
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
        // If we should allow the current user, check if the specified user is this one
        if ($this->allow_self && Auth::id() === (int)$value) {
            return true;
        }

        // Load the actor and see if it exists
        /** @var Actor|null $item */
        $item = Actor::query()->find($value);

        if (is_null($item)) {
            return false;
        }

        /** @var AccessToken|Actor $user */
        $user = Auth::user();

        if (Auth::user() instanceof AccessToken) {
            return true;
        }

        // Finally, check if the current user can access it
        return $user->hasAccessTo($item);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string {
        return 'You do not have access to the specified actor';
    }
}
