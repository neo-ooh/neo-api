<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreTemplateRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\ExtractsTemplates;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Demographics\Models\DatasetVersion;
use Neo\Modules\Demographics\Models\ExtractTemplate;
use Neo\Modules\Demographics\Models\GeographicReportTemplate;
use Neo\Rules\PublicRelations;

class StoreTemplateRequest extends FormRequest {
    public function rules(): array {
        return [
            "name" => ["required", "string"],
            "description" => ["sometimes", "string"],

            "dataset_version_id" => ["required", "integer", new Exists(DatasetVersion::class, "id")],
            "geographic_report_template_id" => ["required", "integer", new Exists(GeographicReportTemplate::class, "id")],

            "with" => ["array", new PublicRelations(ExtractTemplate::class)]
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::datasets_edit->value);
    }
}
