<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowUnavailabilityRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Unavailabilities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\Unavailability;
use Neo\Rules\PublicRelations;

class ShowUnavailabilityRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => ["array", new PublicRelations(Unavailability::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_unavailabilities_view->value);
    }
}
