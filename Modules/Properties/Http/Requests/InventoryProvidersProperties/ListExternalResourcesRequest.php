<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListExternalResourcesRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\InventoryProvidersProperties;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ListExternalResourcesRequest extends FormRequest {
    public function rules(): array {
        return [
            "type"                => ["required", "string", "in:property,product,property-product"],
            "only_not_associated" => ["boolean"],
            "property_id"         => ["required_if:type,product,property-product"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_inventories_edit->value);
    }
}
