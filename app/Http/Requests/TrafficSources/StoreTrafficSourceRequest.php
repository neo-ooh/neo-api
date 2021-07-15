<?php

namespace Neo\Http\Requests\TrafficSources;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Neo\Enums\Capability;

class StoreTrafficSourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows(Capability::traffic_sources);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "type" => ["required", "string", Rule::in(["linkett"])],
            "name" => ["required", "string"],

            "api_key" => ["required_if:type,linkett", "string"]
        ];
    }
}