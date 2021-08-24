<?php

namespace Neo\Http\Requests\BroadcasterConnections;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Neo\Enums\Capability;

class StoreConnectionRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return Gate::allows(Capability::networks_connections);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "name"                => ["required", "string"],
            "type"                => ["required", Rule::in(["broadsign", "pisignage"])],

            // BroadSign connection parameters
            "certificate"         => ["required_if:type,broadsign", "file"],
            "domain_id"           => ["required_if:type,broadsign", "integer"],
            "default_customer_id" => ["required_if:type,broadsign", "nullable", "integer"],
            "default_tracking_id" => ["required_if:type,broadsign", "nullable", "integer"],

            // PiSignage connection parameters
            "server_url"          => ["required_if:type,pisignage", "url"],
            "token"               => ["required_if:type,pisignage", "string"],
        ];
    }
}
