<?php

namespace Neo\Http\Requests\Contracts;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;
use Neo\Models\Contract;

class StoreContractRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        /** @var Contract $contract */
        return Gate::allows(Capability::contracts_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "contract_id" => ["required", "string"],
            "client_id"   => ["required", "exists:clients,id"],
        ];
    }
}
