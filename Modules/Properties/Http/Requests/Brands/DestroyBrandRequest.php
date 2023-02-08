<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DestroyBrandRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class DestroyBrandRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::brands_edit->value);
    }

    public function rules(): array {
        return [];
    }
}
