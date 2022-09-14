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
use Illuminate\Validation\Rules\Exists;
use Neo\Models\Property;

class ListProductsRequest extends FormRequest {
    public function rules(): array {
        return [
            "property_id" => ["integer", new Exists(Property::class, "actor_id")],
            "with"        => ["array"]
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
