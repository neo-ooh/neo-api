<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GetPOPBaseRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\ProofOfPerformances;

use Illuminate\Foundation\Http\FormRequest;

class GetPOPBaseRequest extends FormRequest {
    public function rules(): array {
        return [];
    }

    public function authorize(): bool {
        return true;
    }
}
