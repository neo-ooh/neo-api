<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListScreenTypesRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\ScreenTypes;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\ScreenType;
use Neo\Rules\PublicRelations;

class ListScreenTypesRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => [new PublicRelations(ScreenType::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::screen_types_edit->value)
            || Gate::allows(Capability::products_edit->value)
            || Gate::allows(Capability::product_categories_edit->value);
    }
}
