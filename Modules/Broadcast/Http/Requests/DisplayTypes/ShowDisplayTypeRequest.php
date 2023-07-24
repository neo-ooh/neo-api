<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowDisplayTypeRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\DisplayTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\DisplayType;
use Neo\Rules\PublicRelations;

class ShowDisplayTypeRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::formats_edit->value) ||
            Gate::allows(Capability::formats_crop_frames_edit->value) ||
            Gate::allows(Capability::networks_edit->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "with" => ["sometimes", "array", new PublicRelations(DisplayType::class)],
        ];
    }
}
