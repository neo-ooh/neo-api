<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowSetRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\IndexSets;

use Illuminate\Foundation\Http\FormRequest;
use Neo\Modules\Demographics\Models\IndexSetTemplate;
use Neo\Rules\PublicRelations;

class ShowSetRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => ["array", new PublicRelations(IndexSetTemplate::class)],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
