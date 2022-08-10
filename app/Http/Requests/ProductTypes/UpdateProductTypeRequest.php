<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateProductTypeRequest.php
 */

namespace Neo\Http\Requests\ProductTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateProductTypeRequest extends FormRequest {
    public function rules(): array {
        return [
            "name_en" => ["required", "string"],
            "name_fr" => ["required", "string"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_products->value);
    }
}
