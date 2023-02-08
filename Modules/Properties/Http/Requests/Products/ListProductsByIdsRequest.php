<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListProductsByIdsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\Product;
use Neo\Rules\PublicRelations;

class ListProductsByIdsRequest extends FormRequest {
    public function rules(): array {
        return [
            "ids"   => ["required", "array"],
            "ids.*" => ["integer", new Exists(Product::class, "id")],

            "with" => ["array", new PublicRelations(Product::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::networks_edit->value)
            || Gate::allows(Capability::products_view->value);

    }
}
