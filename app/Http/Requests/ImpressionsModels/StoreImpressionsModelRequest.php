<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListImpressionsModelsRequest.php
 */

namespace Neo\Http\Requests\ImpressionsModels;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreImpressionsModelRequest extends FormRequest {
    public function rules(): array {
        return [
            "start"     => ["requried", "integer", "min:1", "lte:end"],
            "end"       => ["requried", "integer", "gte:start", "max:12"],
            "formula"   => ["required", "string"],
            "variables" => ["required", "array"]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::products_impressions);
    }
}
