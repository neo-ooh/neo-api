<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TrafficRequiredReminder.php
 */

namespace Neo\Jobs\Properties;

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
use Neo\Models\Property;
use Symfony\Component\Console\Output\ConsoleOutput;

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
        $properties = Property::query()
                              ->where("require_traffic", "=", true)
                              ->whereDoesntHave("traffic_data", function (Builder $query) {
                                  $lastMonth = Date::now()->subMonth()->startOfMonth();
                                  $query->where("year", "=", $lastMonth->year)
                                        ->where("month", "=", $lastMonth->month - 1); // Sub one to the month as its index is one-indexed by carbon
                              })->get();

        // For each properties, we need the list of actors responsible for it
        $actors = $properties->map(fn(Property $property) => $property->actor->getActorsInHierarchyWithCapability(Capability::properties_traffic()))
            ->flatten(1)
            ->unique("id");

        // We have a list of actors responsible for inputing traffic data for properties that are missing last month data. And we have removed any duplicate. We can now send the emails.
        foreach($actors as $actor) {
            Mail::to($actor)->send(new TrafficDataReminder($actor));
        }
    }
}
