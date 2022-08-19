<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateScheduleRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\CampaignsSchedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\BroadcastTag;

class UpdateScheduleRequest extends FormRequest {
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
            "start_date"     => ["required", "date:Y-m-d"],
            "start_time"     => ["required", "date:H:m:s"],
            "end_date"       => ["required", "date:Y-m-d"],
            "end_time"       => ["required", "date:H:m:s"],
            "broadcast_days" => ["required", "int", "max:127"],
            "is_locked"      => ["required", "boolean"],
            "tags"           => ["array"],
            "tags.*"         => ["integer", new Exists(BroadcastTag::class)]
        ];
    }
}
