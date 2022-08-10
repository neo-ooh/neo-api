<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateFieldsRequest.php
 */

namespace Neo\Http\Requests\NetworkFields;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateFieldsRequest extends FormRequest {
    public function rules(): array {
        return [
            "fields.*.field_id" => ["required", "exists:fields,id"],
            "fields.*.order"    => ["required", "integer"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_fields->value);
    }
}
