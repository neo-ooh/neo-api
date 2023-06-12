<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListEventsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\InventoryResourceEvents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\InventoryResourceEvent;
use Neo\Rules\PublicRelations;

class ListEventsRequest extends FormRequest {
    public function rules(): array {
        return [
            "page"  => ["integer"],
            "count" => ["integer"],

            "only_failed"  => ["sometimes", "boolean"],
            "inventory_id" => ["sometimes", "integer", new Exists(InventoryProvider::class, "id")],

            "with" => ["array", new PublicRelations(InventoryResourceEvent::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_inventories_view->value);
    }
}
