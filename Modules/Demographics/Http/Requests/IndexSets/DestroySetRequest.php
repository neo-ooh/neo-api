<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DestroySetRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\IndexSets;

use Illuminate\Foundation\Http\FormRequest;

class DestroySetRequest extends FormRequest {
    public function rules(): array {
        return [
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
