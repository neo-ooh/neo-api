<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListReportsRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\GeographicReports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Demographics\Models\GeographicReport;
use Neo\Modules\Demographics\Models\GeographicReportTemplate;
use Neo\Modules\Properties\Models\Property;
use Neo\Rules\PublicRelations;

class ListReportsRequest extends FormRequest {
    public function rules(): array {
        return [
            "property_id" => ["sometimes", new Exists(Property::class, "actor_id")],
            "template_id" => ["sometimes", new Exists(GeographicReportTemplate::class, "id")],

            "page"  => ["integer"],
            "count" => ["integer"],

            "with" => ["array", new PublicRelations(GeographicReport::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::datasets_edit->value);
    }
}
