<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowProductRequest.php
 */

namespace Neo\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\Product;
use Neo\Rules\PublicRelations;

class ShowProductRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => ["array", new PublicRelations(Product::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_edit->value) && Gate::allows(Capability::properties_products->value);
    }
}
