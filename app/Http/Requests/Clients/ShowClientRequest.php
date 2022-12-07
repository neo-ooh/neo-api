<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowClientRequest.php
 */

namespace Neo\Http\Requests\Clients;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\Client;

class ShowClientRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        if (Gate::allows(Capability::contracts_manage->value)) {
            return true;
        }

        // Get the request client
        /** @var Client $client */
        $client = $this->route()?->parameter("client");
        return $client->has("contracts", function (Builder $query) {
            $query->where("owner_id", "=", Auth::id());
        })->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            //
        ];
    }
}
