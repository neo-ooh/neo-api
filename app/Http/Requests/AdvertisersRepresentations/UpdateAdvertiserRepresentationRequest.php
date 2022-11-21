<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateAdvertiserRepresentationRequest.php
 */

namespace Neo\Http\Requests\AdvertisersRepresentations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class UpdateAdvertiserRepresentationRequest extends FormRequest {
    public function rules(): array {
        return [
            "external_id" => ["required", "string"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::advertiser_edit->value);
    }
}
