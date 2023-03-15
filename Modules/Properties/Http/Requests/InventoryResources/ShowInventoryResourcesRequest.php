<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowInventoryResourcesRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\InventoryResources;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\InventoryResource;
use Neo\Rules\PublicRelations;

class ShowInventoryResourcesRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => ["array", new PublicRelations(InventoryResource::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_inventories_view->value);
    }
}
