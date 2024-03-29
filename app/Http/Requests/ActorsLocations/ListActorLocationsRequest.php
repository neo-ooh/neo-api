<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListActorLocationsRequest.php
 */

namespace Neo\Http\Requests\ActorsLocations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Rules\PublicRelations;

class ListActorLocationsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        $gate   = Gate::allows(Capability::actors_edit->value) && Auth::user()->hasAccessTo($this->route("actor"));
        $itself = Auth::user()->is($this->route("actor"));
        return $gate || $itself;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "format_id"  => ["sometimes", "integer", "exists:formats,id"],
            "network_id" => ["sometimes", "integer", "exists:networks,id"],

            "with" => ["array", new PublicRelations(Location::class)],
        ];
    }
}
