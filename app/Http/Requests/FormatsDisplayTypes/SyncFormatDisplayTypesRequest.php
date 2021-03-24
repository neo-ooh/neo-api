<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncFormatDisplayTypesRequest.php
 */

namespace Neo\Http\Requests\FormatsDisplayTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class SyncFormatDisplayTypesRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::formats_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "display_types"   => ["required", "array"],
            "display_types.*" => ["integer", "exists:display_types,id"],
        ];
    }
}