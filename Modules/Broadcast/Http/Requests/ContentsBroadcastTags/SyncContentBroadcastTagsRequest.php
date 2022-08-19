<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncContentBroadcastTagsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\ContentsBroadcastTags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\BroadcastTag;

class SyncContentBroadcastTagsRequest extends FormRequest {
    public function rules(): array {
        return [
            "tags"   => ["array"],
            "tags.*" => ["int", new Exists(BroadcastTag::class, "id")]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::contents_tags->value);
    }
}
