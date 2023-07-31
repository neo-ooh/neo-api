<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreScreenshotRequestRequest.php
 */

namespace Neo\Http\Requests\ScreenshotsRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\Player;
use Neo\Modules\Properties\Models\Product;

class StoreScreenshotRequestRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        return Gate::allows(Capability::screenshots_requests->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [
            "product_id"  => ["sometimes", "integer", new Exists(Product::class, "id")],
            "location_id" => ["sometimes", "integer", new Exists(Location::class, "id")],
            "player_id"   => ["sometimes", "integer", new Exists(Player::class, "id")],

            "send_at"       => ["required", "date"],
            "scale_percent" => ["required", "integer", "min:1", "max:100"],
            "duration_ms"   => ["required", "integer"],
            "frequency_ms"  => ["required", "integer"],
        ];
    }
}
