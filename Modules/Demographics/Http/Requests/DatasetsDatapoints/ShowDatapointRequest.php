<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowDatapointRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\DatasetsDatapoints;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Demographics\Models\DatasetDatapoint;
use Neo\Rules\PublicRelations;

class ShowDatapointRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => ["array", new PublicRelations(DatasetDatapoint::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::datasets_edit->value);
    }
}
