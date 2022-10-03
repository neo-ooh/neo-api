<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListLocationsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Rules\PublicRelations;

class ListLocationsRequest extends FormRequest {
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
            "format_id"  => ["sometimes", "integer", "exists:formats,id"],
            "network_id" => ["sometimes", "integer", "exists:networks,id"],

            "with" => ["array", new PublicRelations(Location::class)],
        ];
    }
}
