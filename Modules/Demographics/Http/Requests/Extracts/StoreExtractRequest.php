<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreExtractRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\Extracts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Demographics\Models\Extract;
use Neo\Modules\Demographics\Models\ExtractTemplate;
use Neo\Modules\Demographics\Models\GeographicReport;
use Neo\Modules\Properties\Models\Property;
use Neo\Rules\PublicRelations;

class StoreExtractRequest extends FormRequest {
    public function rules(): array {
        return [
            "property_id" => ["sometimes", new Exists(Property::class, "actor_id")],
            "template_id" => ["sometimes", new Exists(ExtractTemplate::class, "id")],
            "geographic_report_id" => ["sometimes", new Exists(GeographicReport::class, "id")],

            "with" => ["array", new PublicRelations(Extract::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::datasets_edit->value);
    }
}
