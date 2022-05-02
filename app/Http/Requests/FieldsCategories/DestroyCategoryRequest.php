<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListCategoriesRequest.php
 */

namespace Neo\Http\Requests\FieldsCategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class DestroyCategoryRequest extends FormRequest {
    public function rules(): array {
        return [
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_edit) && Gate::allows(Capability::properties_fields);
    }
}