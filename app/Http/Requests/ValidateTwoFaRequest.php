<?php

namespace Neo\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class ValidateTwoFaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows(Capability::actors_auth);
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
