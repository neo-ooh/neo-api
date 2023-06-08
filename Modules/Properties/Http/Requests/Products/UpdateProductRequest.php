<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateProductRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\PropertyType;
use Neo\Modules\Properties\Models\ScreenType;
use Neo\Rules\PublicRelations;

class UpdateProductRequest extends FormRequest {
    public function rules(): array {
        return [
            "is_sellable"  => ["required", "boolean"],
            "format_id"    => ["nullable", "integer", new Exists(Format::class, "id")],
            "site_type_id" => ["nullable", "integer", new Exists(PropertyType::class, "id")],

            "allowed_media_types"   => ["array"],
            "allowed_media_types.*" => [new Enum(MediaType::class)],
            "allows_audio"          => ["nullable", "boolean"],
            "allows_motion"         => ["nullable", "boolean"],

            "screen_size_in" => ["nullable", "nullable"],
            "screen_type_id" => ["nullable", new Exists(ScreenType::class, "id")],

            "production_cost" => ["nullable", "numeric"],

            "with" => ["array", new PublicRelations(Product::class)],
        ];
    }

    public function authorize(): bool {
        return Capability::products_edit->value;
    }
}
