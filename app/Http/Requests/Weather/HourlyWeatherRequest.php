<?php

namespace Neo\Http\Requests\Weather;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class HourlyWeatherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows(Capability::dynamics_weather);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "country" => ["required", "string", "size:2"],
            "province" => ["required", "string"],
            "city" => ["required", "string"],
            "locale" => ["required", "string", "size:2"]
        ];
    }
}
