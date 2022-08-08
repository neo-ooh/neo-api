<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SetScreensStateRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;

class SetScreensStateRequest extends FormRequest {
    public function rules(): array {
        return [
            "state" => ["required", "boolean"]
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
