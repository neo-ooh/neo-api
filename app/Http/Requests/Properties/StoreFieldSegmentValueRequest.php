<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreFieldSegmentValueRequest.php
 */

namespace Neo\Http\Requests\Properties;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreFieldSegmentValueRequest extends FormRequest {
    public function rules(): array {
        return [
            "segment_id" => ["required", "exists:fields_segments,id"],
            "value" => ["required", "number"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_edit);
    }
}
