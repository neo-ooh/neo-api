<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateImpressionsModelRequest.php
 */

namespace Neo\Http\Requests\ImpressionsModels;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateImpressionsModelRequest extends FormRequest {
    public function rules(): array {
        return [
            "start_month" => ["required", "integer", "min:1", "lte:end_month"],
            "end_month"   => ["required", "integer", "gte:start_month", "max:12"],
            "formula"     => ["required", "string"],
            "variables"   => ["nullable", "array"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::products_impressions->value);
    }
}
