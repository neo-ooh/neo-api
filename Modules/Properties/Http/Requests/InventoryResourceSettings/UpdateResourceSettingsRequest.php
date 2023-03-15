<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateResourceSettingsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\InventoryResourceSettings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateResourceSettingsRequest extends FormRequest {
    public function rules(): array {
        return [
            "is_enabled"   => ["required", "boolean"],
            "pull_enabled" => ["required", "boolean"],
            "push_enabled" => ["required", "boolean"],
            "settings"     => ["nullable", "array"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_inventories_edit->value);
    }
}
