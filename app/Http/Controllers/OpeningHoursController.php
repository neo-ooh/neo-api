<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OpeningHoursController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\OpeningHours\UpdateOpeningHoursRequest;
use Neo\Models\OpeningHours;
use Neo\Models\Property;

class OpeningHoursController {
    public function update(UpdateOpeningHoursRequest $request, Property $property, int $weekday) {
        OpeningHours::query()->updateOrInsert([
            "property_id" => $property->getKey(),
            "weekday"     => $weekday
        ], [
            "open_at"  => $request->input("open_at"),
            "close_at" => $request->input("close_at"),
        ]);

        return new Response([], 202);
    }
}
