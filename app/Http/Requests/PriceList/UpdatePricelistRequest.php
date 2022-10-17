<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdatePricelistRequest.php
 */

namespace Neo\Http\Requests\PriceList;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\Pricelist;
use Neo\Rules\PublicRelations;

class UpdatePricelistRequest extends FormRequest {
    public function rules(): array {
        return [
            "name"        => ["required", "string"],
            "description" => ["nullable", "string"],

            "with" => ["array", new PublicRelations(Pricelist::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::pricelists_edit->value);
    }
}
