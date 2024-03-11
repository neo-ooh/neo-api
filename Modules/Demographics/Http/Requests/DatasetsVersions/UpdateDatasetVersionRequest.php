<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateDatasetVersionRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\DatasetsVersions;

use Illuminate\Foundation\Http\FormRequest;
use Neo\Modules\Demographics\Models\DatasetVersion;
use Neo\Rules\PublicRelations;

class UpdateDatasetVersionRequest extends FormRequest {
    public function rules(): array {
        return [
            "is_primary" => ["required", "boolean"],
            "is_archived" => ["required", "boolean"],
            "order" => ["required", "number"],

            "with" => ["array", new PublicRelations(DatasetVersion::class)],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
