<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DestroyPropertyRequest.php
 */

namespace Neo\Http\Requests\Odoo\Properties;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class DestroyPropertyRequest extends FormRequest {
    public function rules() {
        return [
        ];
    }

    public function authorize() {
        return Gate::allows(Capability::odoo_properties->value);
    }
}
