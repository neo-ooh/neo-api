<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StorePropertyRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Odoo\Properties;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StorePropertyRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::properties_create->value);
    }

    public function rules(): array {
        return [
            "property_id" => ["required", "integer", "exists:properties,actor_id"],
            "odoo_id"     => ["required", "integer"],
        ];
    }
}
