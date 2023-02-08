<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreConnectionRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\BroadcasterConnections;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Services\BroadcasterType;

class StoreConnectionRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return Gate::allows(Capability::networks_connections->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            "name" => ["required", "string"],
            "type" => ["required", new Enum(BroadcasterType::class)],

            "contracts" => ["required", "boolean"],

            ...$this->getBroadcasterOptions(),
        ];
    }

    protected function getBroadcasterOptions(): array {
        $broadcasterType = BroadcasterType::from($this->input("type"));

        return match ($broadcasterType) {
            BroadcasterType::BroadSign => [
                "certificate" => ["required", "file"],
                "domain_id"   => ["required", "int"],
                "customer_id" => ["nullable", "int"],
            ],
            BroadcasterType::PiSignage => [
                "server_url" => ["required", "url"],
                "token"      => ["required", "string"],
            ],
            BroadcasterType::SignageOS => [],
        };
    }
}
