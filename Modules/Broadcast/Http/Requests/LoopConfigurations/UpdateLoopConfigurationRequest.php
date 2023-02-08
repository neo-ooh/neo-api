<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateLoopConfigurationRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\LoopConfigurations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateLoopConfigurationRequest extends FormRequest {
    public function rules(): array {
        return [
            "name"           => ["required", "string"],
            "loop_length_ms" => ["required", "integer"],
            "spot_length_ms" => ["required", "integer", "lte:loop_length_ms"],
            "reserved_spots" => ["required", "integer"],
            "start_date"     => ["required", "date"],
            "end_date"       => ["required", "date"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::loops_edit->value);
    }
}
