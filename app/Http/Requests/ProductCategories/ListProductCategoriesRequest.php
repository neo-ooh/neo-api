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

class ListProductCategoriesRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => ["sometimes", "array"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_products);
    }
}
