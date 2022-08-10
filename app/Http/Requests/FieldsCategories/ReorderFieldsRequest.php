<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReorderFieldsRequest.php
 */

namespace Neo\Http\Requests\FieldsCategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ReorderFieldsRequest extends FormRequest {
    public function rules(): array {
        return [
            "fields"   => ["required", "array"],
            "fields.*" => ["numeric", "exists:fields,id"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_fields->value) && Gate::allows(Capability::properties_edit->value);
    }
}
