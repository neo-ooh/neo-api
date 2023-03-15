<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OpeningHoursController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\OpeningHours\RefreshOpeningHoursRequest;
use Neo\Http\Requests\OpeningHours\UpdateOpeningHoursRequest;
use Neo\Jobs\Properties\PullOpeningHoursJob;
use Neo\Models\OpeningHours;
use Neo\Models\Property;

class OpeningHoursController {
    public function refresh(RefreshOpeningHoursRequest $request, Property $property) {
        $job    = new PullOpeningHoursJob($property->getKey());
        $result = $job->handle();

        return new Response([
                                "success" => !!$result,
                            ]);
    }

    public function update(UpdateOpeningHoursRequest $request, Property $property, int $weekday) {
        OpeningHours::query()->updateOrInsert([
                                                  "property_id" => $property->getKey(),
                                                  "weekday"     => $weekday,
                                              ], [
                                                  "is_closed" => $request->input("is_closed"),
                                                  "open_at"   => $request->input("open_at"),
                                                  "close_at"  => $request->input("close_at"),
                                              ]);

//        PushOpeningHoursJob::dispatch($property->getKey());

        return new Response([], 202);
    }
}
