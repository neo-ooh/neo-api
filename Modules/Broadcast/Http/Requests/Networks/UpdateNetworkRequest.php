<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateNetworkRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Networks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Services\BroadcasterType;

class UpdateNetworkRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::networks_edit->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "name"  => ["required", "string"],
            "color" => ["required", "string"],

            // Broadcaster dependant settings
            ...$this->getBroadcasterOptions(),
        ];
    }

    protected function getBroadcasterOptions(): array {
        /** @var Network $network */
        $network = Network::query()->findOrFail($this->route()?->originalParameter("network"));

        return match ($network->broadcaster_connection->broadcaster) {
            BroadcasterType::BroadSign => [
                "customer_id"            => ["nullable", "int"],
                "root_container_id"      => ["required", "int"],
                "campaigns_container_id" => ["required", "int"],
                "creatives_container_id" => ["required", "int"],
            ],
            BroadcasterType::PiSignage => [],
            BroadcasterType::SignageOS => [],
        };
    }
}
