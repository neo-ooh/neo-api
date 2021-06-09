<?php

namespace Neo\Http\Requests\Contracts;

use Auth;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;
use Neo\Models\Contract;

class RefreshContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /** @var Contract $contract */
        $contract = $this->route("contract");
        return $contract->owner_id === Auth::id() || Gate::allows(Capability::contracts_manage);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
