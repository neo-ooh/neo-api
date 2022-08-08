<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreReviewRequest.php
 */

namespace Neo\Http\Requests\Reviews;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\User;
use Neo\Modules\Broadcast\Models\Schedule;

class StoreReviewRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        $gate = Gate::allows(Capability::contents_review->value);

        /** @var Schedule $schedule */
        $schedule = Schedule::query()->findOrFail($this->route()?->originalParameter("schedule"));

        /** @var User $user */
        $user = Auth::user();

        return $gate && $user->canAccessCampaign($schedule->campaign_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "approved" => ["required", "boolean"],
            "message"  => ["nullable", "string"],
        ];
    }
}
