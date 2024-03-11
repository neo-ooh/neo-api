<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowExtractRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\Extracts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Demographics\Models\Extract;
use Neo\Rules\PublicRelations;

class ShowExtractRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => ["array", new PublicRelations(Extract::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::datasets_edit->value);
    }
}
