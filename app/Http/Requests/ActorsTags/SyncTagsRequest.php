<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SycTagsRequest.php
 */

namespace Neo\Http\Requests\ActorsTags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class SyncTagsRequest extends FormRequest {
    public function rules() {
        return [
            "tags" => ["required", "array", "present", "nullable"]
        ];
    }

    public function authorize() {
        return Gate::allows("actors.edit") || Gate::allows("properties.edit");
    }
}
