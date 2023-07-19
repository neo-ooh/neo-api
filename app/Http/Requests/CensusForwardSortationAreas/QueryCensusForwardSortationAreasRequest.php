<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - QueryCensusForwardSortationAreasRequest.php
 */

namespace Neo\Http\Requests\CensusForwardSortationAreas;

use Illuminate\Foundation\Http\FormRequest;

class QueryCensusForwardSortationAreasRequest extends FormRequest {
    public function rules(): array {
        return [
            "query" => ["required", "string", "min:1", "max:3"],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
