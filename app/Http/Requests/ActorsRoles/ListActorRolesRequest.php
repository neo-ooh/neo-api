<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ListActorRolesRequest.php
 */

namespace Neo\Http\Requests\ActorsRoles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ListActorRolesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * A user can query this route for itself OR, for accessible actors if it has the proper capability
     * @return bool
     */
    public function authorize(): bool {
        $gate = Gate::allows(Capability::actors_edit) && Auth::user()->hasAccessTo($this->route("actor"));
        $itself = Auth::user()->is($this->route("actor"));
        return $gate || $itself;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [];
    }
}
