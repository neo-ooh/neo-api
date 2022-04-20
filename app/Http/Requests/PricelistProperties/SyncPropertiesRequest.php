<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncPropertiesRequest.php
 */

namespace Neo\Http\Requests\PricelistProperties;

use Illuminate\Foundation\Http\FormRequest;

class SyncPropertiesRequest extends FormRequest {
    public function rules(): array {
        return [
            "ids"   => ["array"],
            "ids.*" => ["integer", "exists:properties,actor_id"],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
