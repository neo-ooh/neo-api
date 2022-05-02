<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SearchPointsOfInterestsRequest.php
 */

namespace Neo\Http\Requests\PointsOfInterest;

use Illuminate\Foundation\Http\FormRequest;

class SearchPointsOfInterestsRequest extends FormRequest {
    public function rules(): array {
        return [
            "q"      => ["required", "string", "min:3"],
            "bounds" => ["required", "array"],
            "limit"  => ["sometimes", "integer"]
        ];
    }

    public function authorize(): bool {
        return true;
    }
}