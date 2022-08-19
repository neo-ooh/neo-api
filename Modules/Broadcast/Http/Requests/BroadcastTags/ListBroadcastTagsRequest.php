<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListBroadcastTagsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\BroadcastTags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Enums\BroadcastTagScope;
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Models\BroadcastTag;
use Neo\Rules\PublicRelations;

class ListBroadcastTagsRequest extends FormRequest {
    public function authorize() {
        return Gate::allows(Capability::broadcast_tags->value)
            || Gate::allows(Capability::networks_edit->value)
            || Gate::allows(Capability::campaigns_edit->value)
            || Gate::allows(Capability::contents_schedule->value);
    }

    public function rules() {
        return [
            "scope"   => ["sometimes", new Enum(BroadcastTagScope::class)],
            "types"   => ["sometimes", "array"],
            "types.*" => ["string", new Enum(BroadcastTagType::class)],

            "with" => ["array", new PublicRelations(BroadcastTag::class)],
        ];
    }

}
