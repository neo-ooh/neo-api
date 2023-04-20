<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BatchRequest.php
 */

namespace Neo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchRequest extends FormRequest {
    public function rules(): array {
        return [
            "requests"           => ["required", "array"],
            "requests.*.id"      => ["required", "int"],
            "requests.*.uri"     => ["required", "string"],
            "requests.*.method"  => ["required", Rule::in(["get", "post", "put", "patch", "delete"])],
            "requests.*.payload" => ["sometimes"],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
