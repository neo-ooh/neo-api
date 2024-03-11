<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreTemplateRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\GeographicReportsTemplates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;
use Neo\Modules\Demographics\Models\Enums\GeographicReportType;
use Neo\Modules\Demographics\Models\GeographicReportTemplate;
use Neo\Rules\PublicRelations;

class StoreTemplateRequest extends FormRequest {
    public function rules(): array {
        return [
            "name" => ["required", "string", "max:64"],
            "description" => ["sometimes", "string"],

            "type" => ["required", new Enum(GeographicReportType::class)],
            "configuration" => ["required", "array"],

            "with" => ["array", new PublicRelations(GeographicReportTemplate::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::datasets_edit->value);
    }
}
