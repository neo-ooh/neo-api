<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListFlightsRequest.php
 */

namespace Neo\Http\Requests\ContractsFlights;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\ContractFlight;
use Neo\Modules\Properties\Rules\AccessibleProduct;
use Neo\Modules\Properties\Rules\AccessibleProperty;
use Neo\Rules\PublicRelations;

class ListFlightsRequest extends FormRequest {
    public function rules(): array {
        return [
            "property_id" => ["sometimes", "integer", new AccessibleProperty()],
            "product_id"  => ["sometimes", "integer", new AccessibleProduct()],

            "current" => ["sometimes", "boolean"],
            "past"    => ["sometimes", "boolean"],
            "future"  => ["sometimes", "boolean"],

            "with" => ["array", new PublicRelations(ContractFlight::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::odoo_contracts->value);
    }
}
