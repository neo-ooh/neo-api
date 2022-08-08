<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SwapContentCreativesRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Contents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Rules\PublicRelations;

class SwapContentCreativesRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::contents_edit->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "creatives"   => ["required", "array", "size:2"],
            "creatives.*" => ["required", "integer", "exists:creatives,id"],

            "with" => ["array", new PublicRelations(Content::class)],
        ];
    }
}
