<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListProductCategoriesByIdsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\ProductCategories;

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
        return Gate::allows(Capability::products_view->value)
            || Gate::allows(Capability::product_categories_edit->value)
            || Gate::allows(Capability::planner_access->value)
            || Gate::allows(Capability::contracts_manage->value);
    }
}
