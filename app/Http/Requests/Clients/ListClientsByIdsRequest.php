<?php

namespace Neo\Http\Requests\Clients;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ListClientsByIdsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return Gate::allows(Capability::contracts_edit) || Gate::allows(Capability::contracts_manage);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "ids"   => ["required", "array"],
            "ids.*" => ["exists:clients,id"]
        ];
    }
}