<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateAdvertiserRequest.php
 */

namespace Neo\Http\Requests\Advertisers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateAdvertiserRequest extends FormRequest {
    public function rules(): array {
        return [
            "name"    => ["required", "string"],
            "odoo_id" => ["required", "integer"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::advertiser_edit->value);
    }
}
