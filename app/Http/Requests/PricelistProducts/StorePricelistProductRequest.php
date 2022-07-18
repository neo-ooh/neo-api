<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StorePricelistProductRequest.php
 */

namespace Neo\Http\Requests\PricelistProducts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Enums\PricingType;
use Neo\Models\Product;

class StorePricelistProductRequest extends FormRequest {
    public function rules(): array {
        return [
            "product_id" => ["required", new Exists(Product::class, "id")],
            "pricing"    => ["required", new Enum(PricingType::class)],
            "value"      => ["required", "numeric", "min:0"],
            "min"        => ["nullable", "numeric", "min:0"],
            "max"        => ["nullable", "numeric", "min:0", "gte:min"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::pricelists_edit);
    }
}