<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - QueryCensusDivisionsRequest.php
 */

namespace Neo\Http\Requests\CensusDivisions;

use Illuminate\Foundation\Http\FormRequest;

class QueryCensusDivisionsRequest extends FormRequest {
    public function rules(): array {
        return [
            "query" => ["required", "string"],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
