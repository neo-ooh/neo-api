<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreUnavailabilityRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Unavailabilities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Rules\AccessibleProduct;
use Neo\Modules\Properties\Rules\AccessibleProperty;

class StoreUnavailabilityRequest extends FormRequest {
    public function rules(): array {
        return [
            "start_date" => ["required_if:end_date,null", "nullable", "date"],
            "end_date"   => ["required_if:start_date,null", "nullable", "date"],

            "property"   => ["required_if:product_id,null", "integer", "sometimes", new Exists(Property::class, "actor_id"), new AccessibleProperty()],
            "products"   => ["required_if:property_id,null", "array"],
            "products.*" => ["integer", "sometimes", new Exists(Product::class, "id"), new AccessibleProduct()],

            "translations"           => ["array"],
            "translations.*.locale"  => ["required", "string"],
            "translations.*.reason"  => ["required", "string"],
            "translations.*.comment" => ["nullable", "string"],
        ];
    }

    public function authorize(): bool {
        // If the unavailability is to be associated with a product, make sure the user can access products
        return Gate::allows(Capability::properties_unavailabilities_edit->value)
            && (!$this->has("product_id") || Gate::allows(Capability::products_view->value));
    }
}
