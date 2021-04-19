<?php

namespace Neo\Http\Requests\NewsBackgrounds;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;

class ListBackgroundsRequest extends FormRequest
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
            "network" => ["sometimes", "string"],
            "format_id" => ["sometimes", "integer", "exists:formats,id"],
            "locale" => ["sometimes", "string"]
        ];
    }
}
