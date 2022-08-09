<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateProductCategoryRequest.php
 */

namespace Neo\Http\Requests\ProductCategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;
use Neo\Enums\ProductsFillStrategy;

class UpdateProductCategoryRequest extends FormRequest {
    public function rules(): array {
        return [
            "name_en"       => ["required", "string"],
            "name_fr"       => ["required", "string"],
            "fill_strategy" => ["required", new Enum(ProductsFillStrategy::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_products->value) && Gate::allows(Capability::properties_edit->value);
    }
}
