<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreFieldRequest.php
 */

namespace Neo\Http\Requests\Fields;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Neo\Enums\Capability;

class StoreFieldRequest extends FormRequest {
    public function rules(): array {
        return [
            "name_en" => ["required", "string"],
            "name_fr" => ["required", "string"],
            "type" => ["required", "string"],
            "unit" => ["required", Rule::in(["int", "float", "bool"])],
            "is_filter" => ["required", "boolean"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_fields);
    }
}
