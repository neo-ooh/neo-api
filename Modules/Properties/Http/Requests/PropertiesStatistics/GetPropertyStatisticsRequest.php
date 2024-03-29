<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GetPropertyStatisticsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\PropertiesStatistics;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class GetPropertyStatisticsRequest extends FormRequest {
    public function rules(): array {
        return [
            "years"      => ["required", "array"],
            "years.*"    => ["integer"],
            "breakdown"  => ["required", "string", "in:default,market,product,network"],
            "product_id" => ["required_if:breakdown,product", "integer"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_traffic_manage->value);
    }
}
