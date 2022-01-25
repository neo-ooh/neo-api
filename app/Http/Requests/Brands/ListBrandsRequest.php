<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListBrandsRequest.php
 */

namespace Neo\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;

class ListBrandsRequest extends FormRequest {
    public function rules(): array {
        return [
            "with"   => ["nullable", "array"],
            "with.*" => ["in:properties"],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
