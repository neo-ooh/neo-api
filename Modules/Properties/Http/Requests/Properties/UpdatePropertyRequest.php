<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdatePropertyRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Properties;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\PropertyType;

class UpdatePropertyRequest extends FormRequest {
    public function rules(): array {
        return [
            "network_id"   => ["nullable", "exists:networks,id"],
            "is_sellable"  => ["required", "boolean"],
            "has_tenants"  => ["required", "boolean"],
            "pricelist_id" => ["nullable", "exists:pricelists,id"],
            "website"      => ["nullable", "string"],
            "type_id"      => ["nullable", "integer", new Exists(PropertyType::class, "id")],
            "notes"        => ["nullable", "string"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_create->value);
    }
}
