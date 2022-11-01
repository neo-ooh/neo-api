<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowBroadSignExportRequest.php
 */

namespace Neo\Http\Requests\ContractsFlights;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Models\ProductCategory;

class ShowBroadSignExportRequest extends FormRequest {
    public function rules(): array {
        return [
            "category_id" => ["required", "integer", new Exists(ProductCategory::class, "id")],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::contracts_manage->value);
    }
}
