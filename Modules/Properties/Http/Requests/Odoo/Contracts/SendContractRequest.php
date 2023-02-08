<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SendContractRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Odoo\Contracts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class SendContractRequest extends FormRequest {
    public function rules(): array {
        return [
            "plan"        => ["required", "array"],
            "contract"    => ["required", "string"],
            "clearOnSend" => ["required", "boolean"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::odoo_contracts->value);
    }
}
