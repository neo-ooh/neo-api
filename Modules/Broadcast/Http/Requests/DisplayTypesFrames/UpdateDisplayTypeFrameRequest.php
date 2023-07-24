<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateDisplayTypeFrameRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\DisplayTypesFrames;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateDisplayTypeFrameRequest extends FormRequest {
    public function rules(): array {
        return [
            "name"   => ["required", "string"],
            "pos_x"  => ["required", "numeric", "min:0", "max:1"],
            "pos_y"  => ["required", "numeric", "min:0", "max:1"],
            "width"  => ["required", "numeric", "min:0", "max:1"],
            "height" => ["required", "numeric", "min:0", "max:1"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::formats_crop_frames_edit->value);
    }
}
