<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateConnectionRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\BroadcasterConnections;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\BroadcasterConnection;
use Neo\Modules\Broadcast\Services\BroadcasterType;

class UpdateConnectionRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::networks_connections->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "name"      => ["required", "string"],
            "contracts" => ["required", "boolean"],

            ...$this->getBroadcasterOptions(),
        ];
    }

    protected function getBroadcasterOptions(): array {
        /** @var BroadcasterConnection $broadcaster */
        $broadcaster = BroadcasterConnection::query()->findOrFail($this->route()?->originalParameter("connection"));

        return match ($broadcaster->broadcaster) {
            BroadcasterType::BroadSign => [
                "certificate" => ["sometimes", "file"],
                "domain_id"   => ["required", "int"],
                "customer_id" => ["nullable", "int"],
            ],
            BroadcasterType::PiSignage => [
                "server_url" => ["required", "url"],
                "token"      => ["sometimes", "string"],
            ],
            BroadcasterType::SignageOS => [],
        };
    }
}
