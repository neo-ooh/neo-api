<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowMultiplePropertiesRequest.php
 */

namespace Neo\Http\Requests\PropertiesStatistics;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class ShowMultiplePropertiesRequest extends FormRequest {
    public function rules(): array {
        return [
            "properties" => ["required", "array"],
            "properties.*" => ["integer", "exists:properties,actor_id"],
            "years" => ["required", "array"],
            "years.*" => ["integer"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_edit);
    }
}
