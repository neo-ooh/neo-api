<?php

namespace Neo\Http\Requests\PropertiesTraffic;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class StoreTrafficRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows(Capability::properties_traffic) || Gate::allows(Capability::properties_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "year" => ["required", "integer"],
            "month" => ["required", "numeric"],
            "traffic" => ["required", "numeric"],
            "temporary" => ["sometimes", "nullable", "numeric"],
        ];
    }
}
