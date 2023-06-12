<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
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
        $clientId = $this->route()?->originalParameter("client");
        return Client::query()
                     ->where("id", "=", $clientId)
                     ->whereHas("contracts", function (Builder $query) {
                         $query->where("salesperson_id", "=", Auth::id());
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
