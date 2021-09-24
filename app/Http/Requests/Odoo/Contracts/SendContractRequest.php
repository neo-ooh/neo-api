<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowContractRequest.php
 */

namespace Neo\Http\Requests\Odoo\Contracts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class SendContractRequest extends FormRequest {
    public function rules(): array {
        return [
            "flights" => ["required", "array"],
            "odoo" => ["required", "array"],
            "clearOnSend" => ["required", "boolean"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::odoo_contracts);
    }
}
