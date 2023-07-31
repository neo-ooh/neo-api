<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AssociateScreenshotRequest.php
 */

namespace Neo\Http\Requests\ContractsScreenshots;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Models\ContractFlight;

class AssociateScreenshotRequest extends FormRequest {
    public function rules(): array {
        return [
            "flight_id" => ["required", "integer", new Exists(ContractFlight::class, "id")],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::contracts_edit->value) || Gate::allows(Capability::contracts_manage->value);
    }
}
