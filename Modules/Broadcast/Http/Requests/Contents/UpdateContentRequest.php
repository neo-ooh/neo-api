<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateContentRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Contents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\BroadcastTag;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Rules\AccessibleLibrary;
use Neo\Rules\PublicRelations;

class UpdateContentRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::contents_edit->value)
            || Gate::allows(Capability::contents_review->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "owner_id"   => ["required", "integer"],
            "library_id" => ["required", "integer", new AccessibleLibrary()],
            "name"       => ["nullable", "string"],

            "is_approved"           => ["sometimes", "boolean"],
            "max_schedule_duration" => ["sometimes", "integer"],
            "max_schedule_count"    => ["sometimes", "integer"],

            "tags"   => ["array"],
            "tags.*" => ["int", new Exists(BroadcastTag::class, "id")],

            "with" => ["array", new PublicRelations(Content::class)],
        ];
    }
}
