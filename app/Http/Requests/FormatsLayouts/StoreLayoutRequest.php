<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - StoreLayoutRequest.php
 */

namespace Neo\Http\Requests\FormatsLayouts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreLayoutRequest extends FormRequest {
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
            "format_id"     => ["required", "integer", "exists:formats,id"],
            "name"          => ["required", "string"],
            "is_fullscreen" => ["required", "boolean"],
            "trigger_id"    => ["sometimes", "integer", "exists:broadsign_triggers,id"],
            "separation_id" => ["sometimes", "integer", "exists:broadsign_separations,id"],
        ];
    }
}
