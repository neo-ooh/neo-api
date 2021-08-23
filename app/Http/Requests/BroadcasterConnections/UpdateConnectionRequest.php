<?php

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
            "type"                => ["required", Rule::in(["broadsign", "pisignage", "odoo"])],

            // BroadSign connection parameters
            "certificate"         => ["sometimes", "file"],
            "domain_id"           => ["required_if:type,broadsign", "integer"],
            "default_customer_id" => ["required_if:type,broadsign", "nullable", "integer"],
            "default_tracking_id" => ["required_if:type,broadsign", "nullable", "integer"],

            // PiSignage connection parameters
            "token"               => ["sometimes", "string"],

            // Odoo connection parameters
            "server_url"          => ["required_if:type,odoo", "url"],
            "username"            => ["required_if:type,odoo"],
            "password"            => ["required_if:type,odoo"],
            "database"            => ["required_if:type,odoo"],
        ];
    }
}
