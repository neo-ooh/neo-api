<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StorePropertyRequest.php
 */

namespace Neo\Http\Requests\Odoo\Properties;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class StorePropertyRequest extends FormRequest {
    public function authorize() {
        return Gate::allows(Capability::odoo_properties->value);
    }

    public function rules() {
        return [
            "property_id" => ["required", "integer", "exists:properties,actor_id"],
            "odoo_id"     => ["required", "integer"]
        ];
    }
}
