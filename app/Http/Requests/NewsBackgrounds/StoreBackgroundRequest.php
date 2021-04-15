<?php

namespace Neo\Http\Requests\NewsBackgrounds;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;

class StoreBackgroundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows(Capability::dynamics_news);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "format" => ["required", "string"],
            "locale" => ["required", "string"],
            "category" => ["required", "integer", "min:1", "max:9"],
            "background" => ["required", "image"],
        ];
    }
}
