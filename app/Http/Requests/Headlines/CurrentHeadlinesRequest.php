<?php

namespace Neo\Http\Requests\Headlines;

use Illuminate\Foundation\Http\FormRequest;

class CurrentHeadlinesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // All logged in users are allowed to access the current headline
        return true;
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
