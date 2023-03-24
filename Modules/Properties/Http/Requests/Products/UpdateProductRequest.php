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
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Properties\Models\Product;
use Neo\Rules\PublicRelations;

class UpdateProductRequest extends FormRequest {
    public function rules(): array {
        return [
            "format_id" => ["nullable", "integer", new Exists(Format::class, "id")],

            "with" => ["array", new PublicRelations(Product::class)],
        ];
    }

    public function authorize(): bool {
        return Capability::products_view->value;
    }
}