<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListFormatsRequest.php
 */

namespace Neo\Http\Requests\Formats;

use Illuminate\Foundation\Http\FormRequest;

class ListFormatsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize (): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules (): array {
        return [
            "enabled" => ["sometimes", "boolean", "present"],
            "actor" => ["sometimes", "integer", "exists:actors,id"]
        ];
    }
}
