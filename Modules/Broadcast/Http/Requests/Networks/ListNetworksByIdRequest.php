<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListNetworksRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Networks;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Rules\PublicRelations;

class ListNetworksByIdRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return Gate::allows(Capability::networks_edit->value)
            || Gate::allows(Capability::campaigns_edit->value)
            || Gate::allows(Capability::dynamics_weather->value)
            || Gate::allows(Capability::properties_edit->value)
            || Gate::allows(Capability::planner_access->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "ids"   => ["required", "array"],
            "ids.*" => ["integer", new Exists(Network::class, "id")],

            "with" => ["array", new PublicRelations(Network::class)],
        ];
    }
}
