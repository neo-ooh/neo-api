<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdatePointOfInterestRequest.php
 */

namespace Neo\Http\Requests\PointsOfInterest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdatePointOfInterestRequest extends FormRequest {
    public function rules(): array {
        return [
            "name" => ["required", "string"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::planning_fullaccess) || Gate::allows(Capability::properties_tenants);
    }
}
