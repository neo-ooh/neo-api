<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListSavesRequest.php
 */

namespace Neo\Http\Requests\CampaignPlannerSaves;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ListSavesRequest extends FormRequest {
    public function rules(): array {
        return [
            "page"  => ["integer"],
            "count" => ["integer"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::planner_access->value);
    }
}
