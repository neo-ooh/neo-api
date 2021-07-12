<?php

namespace Neo\Http\Requests\DisplayUnitsPrintsFactors;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class StoreFactorsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return Gate::allows(Capability::tools_prints);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "network_id"       => ["required", "integer", "exists:networks,id"],
            "display_types"    => ["required", "array"],
            "display_types.*"  => ["integer", "exists:display_types,id"],
            "start_month"      => ["required", "integer", "min:1", "max:12"],
            "end_month"        => ["required", "integer", "gte:start_month", "max:12"],
            "product_exposure" => ["required", "numeric", "min:0"],
            "exposure_length"  => ["required", "numeric", "min:0"],
            "loop_length"      => ["required", "numeric", "min:1"],
        ];
    }
}
