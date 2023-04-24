<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExportPropertiesRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Properties;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ExportPropertiesRequest extends FormRequest {
    public function rules(): array {
        return [
            "ids"   => ["required", "array"],
            "ids.*" => ["integer"],
            "level" => ["nullable", "string"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_export->value);
    }
}
