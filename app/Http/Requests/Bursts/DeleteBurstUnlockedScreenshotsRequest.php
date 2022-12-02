<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DeleteBurstUnlockedScreenshotsRequest.php
 */

namespace Neo\Http\Requests\Bursts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class DeleteBurstUnlockedScreenshotsRequest extends FormRequest {
    public function rules(): array {
        return [
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::bursts_request->value);
    }
}
