<?php

namespace Neo\Http\Requests\Networks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;
use Neo\Enums\Capability;
use Neo\Models\BroadcasterConnection;
use Neo\Models\Network;
use Neo\Services\Broadcast\Broadcaster;

class UpdateNetworkRequest extends FormRequest
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

            // Broadsign network settings
            "container_id"              => [static::broadcaster(Broadcaster::BROADSIGN), "integer"],
            "customer_id"               => [static::broadcaster(Broadcaster::BROADSIGN), "integer"],
            "tracking_id"               => [static::broadcaster(Broadcaster::BROADSIGN), "integer"],
            "reservations_container_id" => [static::broadcaster(Broadcaster::BROADSIGN), "integer"],
            "ad_copies_container_id"    => [static::broadcaster(Broadcaster::BROADSIGN), "integer"],
        ];
    }

    public static function broadcaster(string $broadcaster): RequiredIf {
        return Rule::requiredIf(fn() => BroadcasterConnection::query()
                                                             ->findOrFail($this->input("connection_id"))->broadcaster === $broadcaster);
    }
}
