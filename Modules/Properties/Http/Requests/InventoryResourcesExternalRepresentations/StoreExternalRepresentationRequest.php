<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreExternalRepresentationRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\InventoryResourcesExternalRepresentations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\InventoryProvider;

class StoreExternalRepresentationRequest extends FormRequest {
    public function rules(): array {
        return [
            "inventory_id" => ["int", new Exists(InventoryProvider::class, "id")],

            "external_id" => ["required", "string"],
            "context"     => ["nullable", "array"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_inventories_edit->value);
    }
}
