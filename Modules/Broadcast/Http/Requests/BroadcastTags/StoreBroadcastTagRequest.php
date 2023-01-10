<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreBroadcastTagRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\BroadcastTags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Enums\BroadcastTagScope;
use Neo\Modules\Broadcast\Enums\BroadcastTagType;

class StoreBroadcastTagRequest extends FormRequest {
    public function authorize() {
        return Gate::allows(Capability::broadcast_tags->value);
    }

    public function rules() {
        return [
            "type"       => ["required", new Enum(BroadcastTagType::class)],
            "name_en"    => ["required", "string"],
            "name_fr"    => ["required", "string"],
            "scope"      => ["nullable", "array"],
            "scope.*"    => [new Enum(BroadcastTagScope::class)],
            "is_primary" => ["required", "boolean"],
        ];
    }
}
