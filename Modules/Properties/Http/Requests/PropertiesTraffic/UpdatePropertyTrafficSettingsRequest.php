<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdatePropertyTrafficSettingsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\PropertiesTraffic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Enums\TrafficFormat;

class UpdatePropertyTrafficSettingsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::properties_traffic_manage->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "format"         => ["required", new Enum(TrafficFormat::class)],
            "is_required"    => ["required", "boolean"],
            "start_year"     => ["required", "integer"],
            "grace_override" => ["present", "nullable", "date"],
            "input_method"   => ["required", "string", Rule::in(["MANUAL", "LINKETT"])],

            "source_id" => ["required_if:input_method,LINKETT", "exists:traffic_sources,id"],
            "venue_id"  => ["required_if:input_method,LINKETT", "string"],

            "missing_value_strategy" => ["required", "string", Rule::in(["USE_LAST", "USE_PLACEHOLDER"])],
            "placeholder_value"      => ["required", "integer"],
        ];
    }
}
