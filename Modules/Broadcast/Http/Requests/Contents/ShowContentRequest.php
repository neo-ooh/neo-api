<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowContentRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Contents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Rules\PublicRelations;

class ShowContentRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::contents_edit->value);
    }

    public function rules(): array {
        return [
            "with" => ["array", new PublicRelations(Content::class)],
        ];
    }
}
