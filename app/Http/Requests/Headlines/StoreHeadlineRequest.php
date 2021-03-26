<?php

namespace Neo\Http\Requests\Headlines;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class StoreHeadlineRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows(Capability::headlines_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "style" => ["required", "string"],
            "end_date" => ["required", "nullable", "date"],
            "messages" => ["required", "array"],
            "messages.*.locale" => ["required", "string", "max:5"],
            "messages.*.messsage" => ["required", "string", "max:5"],
        ];
    }
}
