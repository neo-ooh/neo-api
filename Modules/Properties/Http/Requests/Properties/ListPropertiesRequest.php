<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListPropertiesRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Properties;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Rules\AccessibleActor;

class ListPropertiesRequest extends FormRequest {
    public function rules(): array {
        return [
            "network_id" => ["integer", new Exists(Network::class, "id")],
            "parent_id"  => ["sometimes", "integer", new AccessibleActor(true)],

            "with" => ["sometimes", "array"],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::properties_view->value)
            || Gate::allows(Capability::planner_access->value);
    }
}
