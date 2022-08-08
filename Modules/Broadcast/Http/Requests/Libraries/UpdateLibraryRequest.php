<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateLibraryRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Libraries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Library;
use Neo\Rules\AccessibleActor;
use Neo\Rules\PublicRelations;

class UpdateLibraryRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::libraries_edit->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "name"          => ["required", "string", "min:3"],
            "owner_id"      => ["required", "integer", new AccessibleActor()],
            "content_limit" => ["required", "integer", "min:0"],

            "formats"   => ["required", "array"],
            "formats.*" => ["integer", new Exists(Format::class)],

            "with" => ["array", new PublicRelations(Library::class)],
        ];
    }
}
