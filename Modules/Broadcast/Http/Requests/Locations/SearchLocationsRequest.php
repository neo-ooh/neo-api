<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SearchLocationsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class SearchLocationsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::locations_edit->value)
            || Gate::allows(Capability::campaigns_edit->value)
            || Gate::allows(Capability::actors_edit->value)
            || Gate::allows(Capability::properties_products->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "q"       => ["nullable", "string"],
            "network" => ["sometimes", "integer", "exists:networks,id"],
            "format"  => ["sometimes", "integer", "exists:formats,id"],
            "actor"   => ["sometimes", "integer", "exists:actors,id"],
        ];
    }
}
