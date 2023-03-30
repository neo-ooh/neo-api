<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GetExportRequest.php
 */

namespace Neo\Http\Requests\ContractsFlights;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\ProductCategory;

class GetExportRequest extends FormRequest {
    public function rules(): array {
        return [
            "category_id"  => ["required", "integer", new Exists(ProductCategory::class, "id")],
            "service_type" => ["required", "string", Rule::in(["broadcaster", "inventory"])],
            "service_id"   => ["required", "integer"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::contracts_manage->value);
    }
}
