<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreCreativeRequest.php
 */

namespace Neo\Http\Requests\Creatives;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\Content;

class StoreCreativeRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        $gate   = Gate::allows(Capability::contents_edit);
        $access = Content::findOrFail($this->input("content_id"))->library->isAccessibleBy(Auth::user());
        return $gate && $access;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "content_id"       => ["required", "integer", "exists:contents,id"],
            "frame_id"         => ["required", "integer", "exists:frames,id"],
            "type"             => ["required", "string"],

            // Static Creative
            "file"             => ["required_if:type,static", "file"],

            // Dynamic Creative
            "name"             => ["required_if:type,dynamic", "string", "min:2"],
            "url"              => ["required_if:type,dynamic", "url"],
            "refresh_interval" => ["required_if:type,dynamic", "integer", "min:5"],
        ];
    }
}
