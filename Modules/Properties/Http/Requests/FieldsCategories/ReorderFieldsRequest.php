<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReorderFieldsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\FieldsCategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ReorderFieldsRequest extends FormRequest {
    public function rules(): array {
        return [
            "fields"   => ["required", "array"],
            "fields.*" => ["numeric", "exists:fields,id"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::fields_edit->value);
    }
}
