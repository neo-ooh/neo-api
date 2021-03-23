<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListActorCapabilitiesRequest.php
 */

namespace Neo\Http\Requests\ActorsCapabilities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ListActorCapabilitiesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * A user can query this route for itself OR, for accessible users if it has the proper capability
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::actors_edit) || $this->route("actor")->id === Auth::id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "all" => ["nullable"]
        ];
    }
}
