<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListPricelistsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\PriceList;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\Pricelist;
use Neo\Rules\PublicRelations;

class ListPricelistsRequest extends FormRequest {
    public function rules(): array {
        return [
            "with" => ["array", new PublicRelations(Pricelist::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::pricelists_edit->value)
            || Gate::allows(Capability::properties_pricelist_assign->value);
    }
}
