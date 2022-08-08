<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreCreativeRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Creatives;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Enums\CreativeType;

class StoreCreativeRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        // Check capability
        return Gate::allows(Capability::contents_edit->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "frame_id"         => ["required", "integer", "exists:frames,id"],
            "type"             => ["required", new Enum(CreativeType::class)],

            // Static Creative
            "file"             => ["required_if:type," . CreativeType::Static->value, "file"],

            // Dynamic Creative
            "name"             => ["required_if:type," . CreativeType::Url->value, "string", "min:2"],
            "url"              => ["required_if:type," . CreativeType::Url->value, "url"],
            "refresh_interval" => ["required_if:type," . CreativeType::Url->value, "integer", "min:5"],
        ];
    }
}
