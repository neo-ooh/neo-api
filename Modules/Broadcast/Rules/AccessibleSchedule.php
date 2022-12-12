<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessibleSchedule.php
 */

namespace Neo\Modules\Broadcast\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\Schedule;

class AccessibleSchedule implements Rule {
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool {
        /** @var Schedule $schedule */
        $schedule = Schedule::withTrashed()->findOrFail($value);

        /** @var Actor|null $user */
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->canAccessCampaign($schedule->campaign_id);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string {
        return 'You do not have access to this schedule';
    }
}
