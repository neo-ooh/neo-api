<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StorePolygonsRequest.php
 */

namespace Neo\Http\Requests\CampaignPlannerSaves;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StorePolygonsRequest extends FormRequest {
    public function rules(): array {
        return [
            "name" => ["required", "string"],
            "data" => ["required", "array"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::tools_planning->value);
    }
}
