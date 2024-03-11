<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListDatapointsRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\DatasetsDatapoints;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Demographics\Models\DatasetDatapoint;
use Neo\Modules\Demographics\Models\DatasetVersion;
use Neo\Rules\PublicRelations;

class ListDatapointsRequest extends FormRequest {
    public function rules(): array {
        return [
            "dataset_version_id" => ["required", new Exists(DatasetVersion::class, "id")],

            "with" => ["array", new PublicRelations(DatasetDatapoint::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::datasets_edit->value);
    }
}
