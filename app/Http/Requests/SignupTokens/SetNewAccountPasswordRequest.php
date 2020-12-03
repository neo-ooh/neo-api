<?php

namespace Neo\Http\Requests\SignupTokens;

use Illuminate\Foundation\Http\FormRequest;

class SetNewAccountPasswordRequest extends FormRequest
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
            "token" => ["required", "string", "size:32", "exists:signup_tokens,token"],
            "password" => ["required", "confirmed", "string", "min:6"],
        ];
    }
}
