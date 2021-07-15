<?php

namespace Neo\Http\Requests\PropertiesTraffic;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Neo\Enums\Capability;

class UpdatePropertyTrafficSettingsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return Gate::allows(Capability::properties_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "is_required"            => ["required", "boolean"],
            "start_year"             => ["required", "integer"],
            "grace_override"         => ["present", "nullable", "date"],
            "input_method"           => ["required", "string", Rule::in(["MANUAL", "LINKETT"])],

            "source_id" => ["required_if:input_method,LINKETT", "exists:traffic_sources,id"],
            "venue_id" => ["required_if:input_method,LINKETT", "string"],

            "missing_value_strategy" => ["required", "string", Rule::in(["USE_LAST", "USE_PLACEHOLDER"])],
            "placeholder_value"      => ["required", "integer"],
        ];
    }
}
