<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ListLocationsRequest.php
 */

namespace Neo\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;

class ListLocationsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize (): bool {
        // Users are allowed to list locations. Anyway, they only get the ones they can access.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules (): array {
        return [
            "with"   => [ "sometimes", "array" ],
            "with.*" => [ "string" ],
            "format" => [ "sometimes", "integer", "exists:formats,id" ],
        ];
    }
}
