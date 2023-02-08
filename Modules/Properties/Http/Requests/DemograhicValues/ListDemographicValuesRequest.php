<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListDemographicValuesRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\DemograhicValues;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\DemographicVariable;

class ListDemographicValuesRequest extends FormRequest {
    public function rules(): array {
        return [
            "variables"   => ["required", "array"],
            "variables.*" => [new Exists(DemographicVariable::class, "id")],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::planner_access->value)
            || Gate::allows(Capability::properties_demographics_view->value);
    }
}
