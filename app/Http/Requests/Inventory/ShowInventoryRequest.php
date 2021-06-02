<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowInventoryRequest.php
 */

namespace Neo\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ShowInventoryRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::inventory_read);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "year"        => ["required", "integer"],
            "network"     => ["required", "integer", "exists:networks,id"],
            "province"    => ["sometimes", "string"],
            "city"        => ["sometimes", "string"],
            "location_id" => ["sometimes", "exists:locations,id"],
            "formats"     => ["array", "required"],
            "formats.*"   => ["integer", "exists:formats,id"],
        ];
    }
}
