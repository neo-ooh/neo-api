<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullInventoryResourceRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\InventoryActions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\InventoryProvider;

class PullInventoryResourceRequest extends FormRequest {
    public function rules(): array {
        return [
            "inventory_id" => ["nullable", "integer", new Exists(InventoryProvider::class, "id")],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_inventories_edit->value);
    }
}
