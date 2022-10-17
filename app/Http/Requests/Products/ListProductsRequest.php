<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListProductsRequest.php
 */

namespace Neo\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Models\Product;
use Neo\Models\ProductCategory;
use Neo\Models\Property;
use Neo\Rules\PublicRelations;

class ListProductsRequest extends FormRequest {
    public function rules(): array {
        return [
            "property_id" => ["integer", new Exists(Property::class, "actor_id")],
            "category_id" => ["integer", new Exists(ProductCategory::class, "id")],

            "with" => ["array", new PublicRelations(Product::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_products->value);
    }
}
