<?php

namespace Neo\Http\Requests\WeatherLocations;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Neo\Enums\Capability;

class UpdateWeatherLocationRequest extends FormRequest
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
            "background_selection" => ["required", "string", Rule::in(["WEATHER", "RANDOM"])],
            "selection_revert_date" => ["required_if:background_selection,RANDOM", "date"]
        ];
    }
}
