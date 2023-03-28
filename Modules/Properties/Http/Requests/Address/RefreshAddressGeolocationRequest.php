<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RefreshAddressGeolocationRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class RefreshAddressGeolocationRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::properties_address_edit->value);
    }

    public function rules(): array {
        return [
        ];
    }
}
