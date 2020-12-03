<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Http\Requests\ActorsShares;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreShareRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize (): bool {
        // Actor needs to be connected , have the `actors_edit` capability and has access to the sharing user
        $gate = Gate::allows(Capability::actors_edit);
        $access = Auth::user()->is($this->route('actor')) || Auth::user()->hasAccessTo($this->route('actor'));
        return $gate && $access;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules (): array {
        return [
            "actor" => [ "required", "integer", "exists:actors,id" ],
        ];
    }
}
