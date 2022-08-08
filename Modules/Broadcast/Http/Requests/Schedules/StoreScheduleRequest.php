<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreScheduleRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Rules\AccessibleCampaign;
use Neo\Modules\Broadcast\Rules\AccessibleContent;

class StoreScheduleRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::contents_schedule->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "campaigns"   => ["required", "array"],
            "campaigns.*" => ["int", new AccessibleCampaign()],

            "content_id"      => ["required", "integer", new AccessibleContent()],
            "start_date"      => ["required", "date:Y-m-d"],
            "start_time"      => ["required", "date:H:m:s"],
            "end_date"        => ["required", "date:Y-m-d"],
            "end_time"        => ["required", "date:H:m:s"],
            "broadcast_days"  => ["required", "int", "max:127"],
            "send_for_review" => ["required", "boolean"],
        ];
    }
}
