<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListTagsRequest.php
 */

namespace Neo\Http\Requests\Tags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreTagRequest extends FormRequest {
    public function rules(): array {
        return [
            "name" => ["required", "string"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows('actors.edit');
    }
}
