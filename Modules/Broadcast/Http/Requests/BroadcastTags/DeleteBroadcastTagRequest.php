<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DeleteBroadcastTagRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\BroadcastTags;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class DeleteBroadcastTagRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::broadcast_tags->value);
    }

    public function rules(): array {
        return [
        ];
    }
}
