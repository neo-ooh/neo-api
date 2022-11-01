<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MoveContentsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\LibrariesContents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Rules\AccessibleContent;

class MoveContentsRequest extends FormRequest {
    public function rules(): array {
        return [
            "contents"   => ["required", "array"],
            "contents.*" => ["integer", new AccessibleContent()],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::contents_edit->value);
    }
}
