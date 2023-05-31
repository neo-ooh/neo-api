<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StorePropertyTypeRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\PropertyTypes;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class StorePropertyTypeRequest extends FormRequest {
    public function rules(): array {
        return [
            "name_en" => ["required", "string"],
            "name_fr" => ["required", "string"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::property_types_edit->value);
    }
}
