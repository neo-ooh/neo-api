<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListNetworksRequest.php
 */

namespace Neo\Http\Requests\Networks;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class ListNetworksRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return Gate::allows(Capability::networks_edit)
            || Gate::allows(Capability::campaigns_edit)
            || Gate::allows(Capability::dynamics_weather)
            || Gate::allows(Capability::properties_edit)
            || Gate::allows(Capability::tools_planning);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "with"  => ["sometimes", "array"],
            "actor" => ["sometimes", "integer", "exists:actors,id"]
        ];
    }
}
