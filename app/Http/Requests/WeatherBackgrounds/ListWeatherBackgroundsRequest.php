<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListWeatherBackgroundsRequest.php
 */

namespace Neo\Http\Requests\WeatherBackgrounds;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ListWeatherBackgroundsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return Gate::allows(Capability::dynamics_weather->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "period"     => ["required", "string"],
            "network_id" => ["nullable"],
            "country"    => ["required", "string", "size:2"],
            "province"   => ["required", "string", "max:2"],
            "city"       => ["required", "string"],
            "format_id"  => ["required", "exists:formats,id"]
        ];
    }
}
