<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListBroadcastTagsByIdRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\BroadcastTags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\BroadcastTag;
use Neo\Rules\PublicRelations;

class ListBroadcastTagsByIdRequest extends FormRequest {
    public function authorize() {
        return Gate::allows(Capability::broadcast_tags->value)
            || Gate::allows(Capability::networks_edit->value)
            || Gate::allows(Capability::campaigns_edit->value)
            || Gate::allows(Capability::contents_schedule->value);
    }

    public function rules() {
        return [
            "ids"   => ["array"],
            "ids.*" => ["int", new Exists(BroadcastTag::class, "id")],


            "with" => ["array", new PublicRelations(BroadcastTag::class)],
        ];
    }

}
