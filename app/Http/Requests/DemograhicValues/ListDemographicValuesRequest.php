<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListDemographicValuesRequest.php
 */

namespace Neo\Http\Requests\DemograhicValues;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Models\DemographicVariable;

class ListDemographicValuesRequest extends FormRequest {
    public function rules(): array {
        return [
            "variables"   => ["required", "array"],
            "variables.*" => [new Exists(DemographicVariable::class, "id")],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::tools_planning->value) || Gate::allows(Capability::properties_view->value) || Gate::allows(Capability::properties_fields->value);
    }
}
