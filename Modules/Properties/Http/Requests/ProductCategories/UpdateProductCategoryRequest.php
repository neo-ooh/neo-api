<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateProductCategoryRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\ProductCategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Properties\Enums\ProductsFillStrategy;

class UpdateProductCategoryRequest extends FormRequest {
    public function rules(): array {
        return [
            "name_en"       => ["required", "string"],
            "name_fr"       => ["required", "string"],
            "fill_strategy" => ["required", new Enum(ProductsFillStrategy::class)],
            "format_id"     => ["nullable", new Exists(Format::class, "id")],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::product_categories_edit->value);
    }
}
