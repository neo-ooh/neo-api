<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListLibraryContentsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\LibrariesContents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ListLibraryContentsRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::contents_schedule->value)
            || Gate::allows(Capability::contents_edit->value)
            || Gate::allows(Capability::libraries_edit->value);
    }

    public function rules(): array {
        return [

        ];
    }
}
