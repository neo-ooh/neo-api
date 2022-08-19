<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncLoopConfigurationsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\FormatsLoopConfigurations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\LoopConfiguration;

class SyncLoopConfigurationsRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::formats_edit->value);
    }

    public function rules(): array {
        return [
            "loop_configurations"   => ["array"],
            "loop_configurations.*" => ["int", new Exists(LoopConfiguration::class, "id")],
        ];
    }
}
