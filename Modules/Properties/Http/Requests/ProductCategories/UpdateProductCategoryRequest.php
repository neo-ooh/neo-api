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
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Enums\ProductType;

class UpdateProductCategoryRequest extends FormRequest {
    public function rules(): array {
        return [
            "name_en" => ["required", "string"],
            "name_fr" => ["required", "string"],
            "type"    => ["required", new Enum(ProductType::class)],

            "format_id"             => ["nullable", new Exists(Format::class, "id")],
            "allows_audio"          => ["boolean"],
            "allowed_media_types"   => ["array"],
            "allowed_media_types.*" => [new Enum(MediaType::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::product_categories_edit->value);
    }
}
