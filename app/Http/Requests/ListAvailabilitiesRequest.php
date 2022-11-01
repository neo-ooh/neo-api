<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListAvailabilitiesRequest.php
 */

namespace Neo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListAvailabilitiesRequest extends FormRequest {
    public function rules(): array {
        return [
            "product_ids"     => ["required", "array"],
            "product_ids.*"   => ["integer"],
            "product_spots"   => ["required", "array"],
            "product_spots.*" => ["numeric"],
            "from"            => ["required", "date"],
            "to"              => ["required", "date"],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
