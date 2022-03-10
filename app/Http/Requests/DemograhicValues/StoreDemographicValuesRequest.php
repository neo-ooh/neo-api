<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreDemographicValuesRequest.php
 */

namespace Neo\Http\Requests\DemograhicValues;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreDemographicValuesRequest extends FormRequest {
    public function rules(): array {
        return [
            "files"          => ["required", "array"],
            "files.*.file"   => ["required", "file"],
            "files.*.format" => ["required", "string"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_edit);
    }
}
