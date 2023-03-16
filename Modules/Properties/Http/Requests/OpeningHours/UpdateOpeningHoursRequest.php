<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateOpeningHoursRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\OpeningHours;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateOpeningHoursRequest extends FormRequest {
    public function rules(): array {
        return [
            "is_closed" => ["required", "boolean"],
            "open_at"   => ["required", "regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/"],
            "close_at"  => ["required", "regex:/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_opening_hours_edit->value);
    }
}
