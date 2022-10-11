<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListContentsByIdsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Contents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Rules\PublicRelations;

class ListContentsByIdsRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::contents_schedule->value);
    }

    public function rules(): array {
        return [
            "ids"  => ["required", "array"],
            "with" => ["array", new PublicRelations(Content::class)],
        ];
    }
}
