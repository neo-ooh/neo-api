<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListPropertiesRequest.php
 */

namespace Neo\Http\Requests\Properties;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class ListPropertiesRequest extends FormRequest {
    public function rules() {
        return [
            "with" => ["sometimes", "array"]
        ];
    }

    public function authorize() {
        return Gate::allows(Capability::tools_planning->value);
    }
}
