<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListProductCategoriesRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\ProductCategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Rules\PublicRelations;

class ListProductCategoriesRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => ["array", new PublicRelations(ProductCategory::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::product_categories_edit->value)
            || Gate::allows(Capability::planner_access->value);
    }
}
