<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateConnectionRequest.php
 */

namespace Neo\Http\Requests\BroadcasterConnections;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Neo\Enums\Capability;

class UpdateConnectionRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::networks_connections);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "name"                => ["required", "string"],
            "type"                => ["required", Rule::in(["broadsign", "pisignage"])],

            // BroadSign connection parameters
            "certificate"         => ["sometimes", "file"],
            "domain_id"           => ["required_if:type,broadsign", "integer"],
            "default_customer_id" => ["required_if:type,broadsign", "nullable", "integer"],
            "default_tracking_id" => ["required_if:type,broadsign", "nullable", "integer"],

            // PiSignage connection parameters
            "token"               => ["sometimes", "string"],
        ];
    }
}
