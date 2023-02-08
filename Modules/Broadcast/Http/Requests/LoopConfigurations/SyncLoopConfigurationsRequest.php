<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncLoopConfigurationsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\LoopConfigurations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class SyncLoopConfigurationsRequest extends FormRequest {
    public function rules(): array {
        return [
            "ids"   => ["array"],
            "ids.*" => ["required", "exists:loop_configurations,id"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_products->value) && Gate::allows(Capability::properties_edit->value);
    }
}
