<?php

namespace Neo\Http\Requests\Networks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Neo\Enums\Capability;
use Neo\Models\BroadcasterConnection;
use Neo\Services\Broadcast\Broadcaster;

class StoreNetworkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows(Capability::networks_edit);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "name" => ["required", "string"],
            "connection_id" => ["required", "exists:broadcasters_connections,id"],

            // Broadsign network settings
            "container_id" => [Rule::requiredIf(fn() => BroadcasterConnection::query()->findOrFail($this->route("connection_id"))->broadcaster === Broadcaster::BROADSIGN), "integer"],
            "customer_id" => [Rule::requiredIf(fn() => BroadcasterConnection::query()->findOrFail($this->route("connection_id"))->broadcaster === Broadcaster::BROADSIGN), "integer"],
            "tracking_id" => [Rule::requiredIf(fn() => BroadcasterConnection::query()->findOrFail($this->route("connection_id"))->broadcaster === Broadcaster::BROADSIGN), "integer"],
        ];
    }
}
