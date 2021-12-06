<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SearchPlacesRequest.php
 */

namespace Neo\Http\Requests\Foursquare;

use Illuminate\Foundation\Http\FormRequest;

class SearchPlacesRequest extends FormRequest {
    public function rules(): array {
        return [
            "q"      => ["required", "string", "min:3"],
            "bounds" => ["required", "array"]
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
