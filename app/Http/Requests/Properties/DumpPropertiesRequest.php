<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DumpPropertiesRequest.php
 */

namespace Neo\Http\Requests\Properties;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class DumpPropertiesRequest extends FormRequest {
    public function rules(): array {
        return [
            "network_id" => ["required", "integer", "exists:networks,id"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_export);
    }
}
