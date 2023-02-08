<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreCategoryRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\FieldsCategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreCategoryRequest extends FormRequest {
    public function rules(): array {
        return [
            "name_en" => ["required", "string"],
            "name_fr" => ["required", "string"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::fields_edit->value);
    }
}
