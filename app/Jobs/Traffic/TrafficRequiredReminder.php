<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TrafficRequiredReminder.php
 */

namespace Neo\Jobs\Traffic;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Mail;
use Neo\Enums\Capability;
use Neo\Mails\TrafficDataReminder;
use Neo\Modules\Properties\Models\Property;

/**
 * This job identifies which user are required to input traffic informations, and sends them an email about it.
 */
class TrafficRequiredReminder implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void {
        // We take all properties that require traffic informations and whose past month traffic data is missing
        $lastMonth  = Date::now()->subMonth()->startOfMonth();
        $properties = Property::query()
                              ->whereHas("traffic", function (Builder $query) {
                                  $query->where("is_required", "=", true);
                              })
            // Either no data at all for the past month
                              ->whereDoesntHave("traffic.data", function (Builder $query) use ($lastMonth) {
                $query->where("year", "=", $lastMonth->year)
                      ->where("month", "=", $lastMonth->month - 1); // Sub one to the month as its index is one-indexed by carbon
            })
            // Or no traffic data for the past month (temporary/internal data may be present but doesn't count)
                              ->orWhereHas("traffic.data", function (Builder $query) use ($lastMonth) {
                $query->where("year", "=", $lastMonth->year)
                      ->where("month", "=", $lastMonth->month - 1)
                      ->whereNull("traffic");
            })->get();

        // For each properties, we need the list of actors responsible for it
        $actors = $properties->map(fn(Property $property) => $property->actor->getActorsInHierarchyWithCapability(Capability::properties_traffic))
                             ->flatten(1)
                             ->unique("id");

        $date = Date::now()->subMonth();

        // We have a list of actors responsible for inputing traffic data for properties that are missing last month data. And we have removed any duplicate. We can now send the emails.
        foreach ($actors as $actor) {
            Mail::to($actor)->send(new TrafficDataReminder($actor, $date));
        }
    }
}
