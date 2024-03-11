<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListAreasRequest.php
 */

namespace Neo\Modules\Demographics\Http\Requests\Areas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Exists;
use Neo\Modules\Demographics\Models\Area;
use Neo\Modules\Demographics\Models\AreaType;
use Neo\Rules\PublicRelations;

class ListAreasRequest extends FormRequest {
    public function rules(): array {
        return [
            "type" => ["required", new Exists(AreaType::class, "id")],

            "page"  => ["integer"],
            "count" => ["integer"],

            "with" => ["array", new PublicRelations(Area::class)],
        ];
    }

    public function authorize(): bool {
        return true;
    }
}
