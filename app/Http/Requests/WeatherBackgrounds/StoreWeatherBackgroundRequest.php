<?php

namespace Neo\Http\Requests\WeatherBackgrounds;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreWeatherBackgroundRequest extends FormRequest
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
            "province" => ["required", "string", "max:2"],
            "city" => ["required", "string"],
            "period" => ["required", "string"],
            "network_id" => ["required", "nullable"],
            "weather" => ["required_unless:period,RANDOM", "string"],
            "format_id" => ["required", "exists:formats,id"],

            "background" => ["required", "file", "image"]
        ];
    }
}
