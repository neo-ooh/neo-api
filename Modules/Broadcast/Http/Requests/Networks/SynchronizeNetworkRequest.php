<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeNetworkRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Networks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class SynchronizeNetworkRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows(Capability::networks_refresh->value);
    }

    public function rules(): array {
        return [

        ];
    }
}
