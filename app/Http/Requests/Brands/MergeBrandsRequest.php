<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreBrandRequest.php
 */

namespace Neo\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class MergeBrandsRequest extends FormRequest {
    public function rules(): array {
        return [
            "from"     => ["required", "array"],
            "from.*"   => ["integer", "exists:brands,id"],
            "receiver" => ["required", "integer", "exists:brands,id"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_edit);
    }
}
