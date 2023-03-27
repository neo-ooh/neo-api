<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateInventoryRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\InventoryProviders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\InventoryType;

class UpdateInventoryRequest extends FormRequest {
    public function rules(): array {
        return [
            "name"      => ["required", "string", "min:3"],
            "is_active" => ["required", "boolean"],

            "auto_pull" => ["required", "boolean"],
            "auto_push" => ["required", "boolean"],

            ...$this->getInventoryOptions(),
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::inventories_edit->value);
    }

    protected function getInventoryOptions(): array {
        /** @var InventoryProvider $inventory */
        $inventory = InventoryProvider::query()->findOrFail($this->route()->originalParameter("inventoryProvider"));

        return match ($inventory->provider) {
            InventoryType::Odoo      => [

                "api_url"      => ["required", "string"],
                "api_key"      => ["required", "string"],
                "api_username" => ["required", "string"],
                "database"     => ["required", "string"],
            ],
            InventoryType::Hivestack => [],
            InventoryType::Reach     => [],
            InventoryType::Vistar    => [],
            InventoryType::Atedra    => [],
            InventoryType::Dummy     => [],
        };
    }
}
