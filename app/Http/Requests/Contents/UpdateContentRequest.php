<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - UpdateContentRequest.php
 */

namespace Neo\Http\Requests\Contents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Rules\AccessibleLibrary;

class UpdateContentRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::contents_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "owner_id"            => ["required", "integer"],
            "library_id"          => ["required", "integer", new AccessibleLibrary()],
            "name"                => ["nullable", "string"],
            "is_approved"         => ["sometimes", "boolean"],
            "scheduling_duration" => ["sometimes", "numeric"],
            "scheduling_times"    => ["sometimes", "integer"],
        ];
    }
}
