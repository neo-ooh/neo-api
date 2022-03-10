<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateProductLocationsRequest.php
 */

namespace Neo\Http\Requests\ProductLocations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateProductLocationsRequest extends FormRequest {
    public function rules(): array {
        return [
            "locations"   => ["required", "array"],
            "locations.*" => ["integer", "exists:locations,id"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_products);
    }
}
