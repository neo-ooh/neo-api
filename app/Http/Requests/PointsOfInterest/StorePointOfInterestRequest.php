<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StorePointOfInterestRequest.php
 */

namespace Neo\Http\Requests\PointsOfInterest;

use GeoJson\Geometry\Point;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Rules\GeoJsonRule;

class StorePointOfInterestRequest extends FormRequest {
    public function rules(): array {
        return [
            "name"        => ["required", "string"],
            "address"     => ["required", "string"],
            "external_id" => ["nullable", "string"],
            "position"    => ["required", new GeoJsonRule(Point::class)],
        ];
    }

    public function authorize(): bool {
        return Gate::allows(Capability::tools_planning) || Gate::allows(Capability::properties_tenants);
    }
}
