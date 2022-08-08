<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateExternalRepresentationsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\BroadcastTags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\BroadcasterConnection;

class UpdateExternalRepresentationsRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::broadcast_tags->value);
    }

    public function rules(): array {
        return [
            "representations"                  => ["array"],
            "representations.*.broadcaster_id" => ["required", "int", new Exists(BroadcasterConnection::class, "id")],
            "representations.*.external_id"    => ["required", "string"],
        ];
    }

}
