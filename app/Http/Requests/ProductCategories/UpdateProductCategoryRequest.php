<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListProductCategoriesRequest.php
 */

namespace Neo\Http\Requests\ProductCategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateProductCategoryRequest extends FormRequest {
    public function rules(): array {
        return [
            "name_en"       => ["required", "string"],
            "name_fr"       => ["required", "string"],
            "fill_strategy" => ["required", "in:DEFAULT,FIRST_AVAILABLE"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_products);
    }
}
