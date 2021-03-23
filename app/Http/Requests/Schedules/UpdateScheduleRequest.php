<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateScheduleRequest.php
 */

namespace Neo\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateScheduleRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize (): bool {
        $allowed = Gate::allows(Capability::contents_schedule);
        $campaign = Auth::user()->canAccessCampaign($this->route("schedule")->campaign);
        return $allowed && $campaign;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules (): array {
        return [
            "start_date" => [ "required", "date" ],
            "end_date"   => [ "required", "date" ],
            "locked"     => [ "sometimes", "boolean" ],
        ];
    }
}
