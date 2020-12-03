<?php
//------------------------------------------------------------------------------
// Copyright 2020 (c) Neo-OOH - All Rights Reserved
// Unauthorized copying of this file, via any medium is strictly prohibited
// Proprietary and confidential
// Written by Valentin Dufois <Valentin Dufois>
//
// neo-auth - CheckRecoveryTokenRequest.php
//------------------------------------------------------------------------------

namespace Neo\Http\Requests\PasswordRecoveries;

use Illuminate\Foundation\Http\FormRequest;

class CheckRecoveryTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "token" => ["required", "string", "size:32"]
        ];
    }
}
