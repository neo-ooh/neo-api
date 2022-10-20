<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListProductCategoriesByIdsRequest.php
 */

namespace Neo\Http\Requests\ProductCategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ListProductCategoriesByIdsRequest extends FormRequest {
    public function rules(): array {
        return [
            "ids"  => ["required", "array"],
            "with" => ["sometimes", "array"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_products->value)
            || Gate::allows(Capability::tools_planning->value)
            || Gate::allows(Capability::contracts_manage->value);
    }
}
