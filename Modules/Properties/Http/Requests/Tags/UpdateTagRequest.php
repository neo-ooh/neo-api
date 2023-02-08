<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateTagRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Tags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateTagRequest extends FormRequest {
    public function rules(): array {
        return [
            "name" => ["required", "string"],
            //            "color" => ["required", "string"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_tags_edit->value);
    }
}
