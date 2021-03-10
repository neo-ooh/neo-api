<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SearchLocationsRequest.php
 */

namespace Neo\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;

class SearchLocationsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "q"            => ["required", "string"],
            "display_unit" => ["sometimes", "integer", "exists:display_units,id"],
        ];
    }
}
