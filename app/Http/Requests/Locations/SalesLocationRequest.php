<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListLocationsRequest.php
 */

namespace Neo\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class SalesLocationRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        // Users with sales/reports capabilities are allowed to query this route
        return Gate::allows(Capability::inventory_read);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "with"      => ["sometimes", "array"],
            "with.*"    => ["string"],
            "format"    => ["sometimes", "integer", "exists:formats,id"],
            "container" => ["sometimes", "integer"]
        ];
    }
}
