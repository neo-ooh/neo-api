<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListProductsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Enums\ProductType;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Modules\Properties\Models\Property;
use Neo\Rules\AccessibleActor;
use Neo\Rules\PublicRelations;

class ListProductsRequest extends FormRequest {
    public function rules(): array {
        return [
            "parent_id"   => ["sometimes", "integer", new AccessibleActor(true)],
            "property_id" => ["integer", new Exists(Property::class, "actor_id")],
            "category_id" => ["integer", new Exists(ProductCategory::class, "id")],

            "type" => [new Enum(ProductType::class)],

            "with" => ["array", new PublicRelations(Product::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::products_view->value);
    }
}
