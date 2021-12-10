<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportMappingsRequest.php
 */

namespace Neo\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class ImportMappingsRequest extends FormRequest {
    public function rules(): array {
        return [
            "file"              => ["required", "file"],
            "products_col"      => ["required", "integer"],
            "display_units_col" => ["required", "integer"],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
