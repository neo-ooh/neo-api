<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncTagsRequest.php
 */

namespace Neo\Http\Requests\ActorsTags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class SyncTagsRequest extends FormRequest {
    public function rules() {
        return [
            "tags" => ["array", "nullable", "exists:tags,id"]
        ];
    }

    public function authorize() {
        return Gate::allows(Capability::actors_edit->value) || Gate::allows(Capability::properties_edit->value);
    }
}
