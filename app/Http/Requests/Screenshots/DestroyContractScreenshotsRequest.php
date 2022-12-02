<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DestroyContractScreenshotsRequest.php
 */

namespace Neo\Http\Requests\Screenshots;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class DestroyContractScreenshotsRequest extends FormRequest {
    public function rules(): array {
        return [
            "delete_locked" => ["boolean"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::bursts_request->value);
    }
}
