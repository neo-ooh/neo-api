<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateUnavailabilityRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Unavailabilities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateUnavailabilityRequest extends FormRequest {
    public function rules(): array {
        return [
            "start_date" => ["required_if:end_date,null", "nullable", "date"],
            "end_date"   => ["required_if:start_date,null", "nullable", "date"],
            
            "translations"           => ["array"],
            "translations.*.locale"  => ["required", "string"],
            "translations.*.reason"  => ["required", "string"],
            "translations.*.comment" => ["nullable", "string"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_unavailabilities_view->value);
    }
}
