<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreInventoryRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\InventoryProviders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Services\InventoryType;

class StoreInventoryRequest extends FormRequest {
    public function rules(): array {
        return [
            "name"     => ["required", "string", "min:3"],
            "provider" => ["required", new Enum(InventoryType::class)],

            "auto_pull" => ["required", "boolean"],
            "auto_push" => ["required", "boolean"],

            ...$this->getInventoryOptions(),
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::inventories_edit->value);
    }

    protected function getInventoryOptions(): array {
        $inventoryType = InventoryType::from($this->input("provider"));

        return match ($inventoryType) {
            InventoryType::Odoo          => [
                "api_url"      => ["required", "string"],
                "api_key"      => ["required", "string"],
                "api_username" => ["required", "string"],
                "database"     => ["required", "string"],
            ],
            InventoryType::Hivestack     => [
                "api_url" => ["required", "string"],
                "api_key" => ["required", "string"],
            ],
            InventoryType::PlaceExchange => [
                "api_url"      => ["required", "string"],
                "api_key"      => ["required", "string"],
                "api_username" => ["required", "string"],
                "client_id"    => ["required", "string"],
            ],
            InventoryType::Vistar        => [
                "api_url"      => ["required", "string"],
                "api_key"      => ["required", "string"],
                "api_username" => ["required", "string"],
            ],
            InventoryType::Reach         => [
                "api_url"      => ["required", "string"],
                "auth_url"     => ["required", "string"],
                "api_key"      => ["required", "string"],
                "api_username" => ["required", "string"],
                "publisher_id" => ["required", "string"],
                "client_id"    => ["required", "string"],
            ],
            InventoryType::Dummy         => [],
        };
    }
}
