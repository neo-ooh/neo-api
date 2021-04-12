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

class StoreCreativeRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        $gate   = Gate::allows(Capability::contents_edit);
        $access = $this->route("content")->library->isAccessibleBy(Auth::user());
        return $gate && $access;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "frame_id" => ["required", "integer", "exists:frames,id"],
            "type"     => ["required", "string"],
            "file"     => ["required_if:type,static", "file"],
            "name"      => ["required_if:type,dynamic", "string", "min:2"],
            "url"      => ["required_if:type,dynamic", "url"],
        ];
    }
}
