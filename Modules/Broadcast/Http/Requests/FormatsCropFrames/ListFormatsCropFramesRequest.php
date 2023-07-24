<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListFormatsCropFramesRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\FormatsCropFrames;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ListFormatsCropFramesRequest extends FormRequest {
    public function rules(): array {
        return [
            
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::formats_crop_frames_edit->value);
    }
}
