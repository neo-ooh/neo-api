<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StorePictureRequest.php
 */

namespace Neo\Http\Requests\PropertiesPictures;

use Illuminate\Auth\Access\Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class StorePictureRequest extends FormRequest {
    public function rules(): array {
        return [
            "picture" => ["required", "image"]
        ];
    }

    public function authorize(): bool {
        return \Illuminate\Support\Facades\Gate::allows(Capability::properties_edit->value);
    }
}
