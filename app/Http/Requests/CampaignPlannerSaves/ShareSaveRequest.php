<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShareSaveRequest.php
 */

namespace Neo\Http\Requests\CampaignPlannerSaves;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ShareSaveRequest extends FormRequest {
    public function rules(): array {
        return [
            "actors"   => ["required", "array"],
            "actors.*" => ["integer", "exists:actors,id"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::planner_access->value);
    }
}
