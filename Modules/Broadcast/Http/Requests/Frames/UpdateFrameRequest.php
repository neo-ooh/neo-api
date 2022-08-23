<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateFrameRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Frames;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\BroadcastTag;

class UpdateFrameRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::formats_edit->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "name"   => ["required", "string"],
            "width"  => ["required", "integer", "min:1"],
            "height" => ["required", "integer", "min:1"],
            "tags"   => ["array"],
            "tags.*" => ["integer", new Exists(BroadcastTag::class, "id")],
        ];
    }
}
