<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateTemplateRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\ExtractsTemplates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Demographics\Models\ExtractTemplate;
use Neo\Rules\PublicRelations;

class UpdateTemplateRequest extends FormRequest {
    public function rules(): array {
        return [
            "name" => ["required", "string"],
            "description" => ["sometimes", "string"],

            "with" => ["array", new PublicRelations(ExtractTemplate::class)]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::datasets_edit->value);
    }
}
