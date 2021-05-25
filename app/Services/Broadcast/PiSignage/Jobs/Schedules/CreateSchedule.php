<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeLocations.php
 */

namespace Neo\Services\Broadcast\PiSignage\Jobs\Schedules;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Campaign;
use Neo\Models\Creative;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;
use Neo\Services\Broadcast\PiSignage\Models\Asset;
use Neo\Services\Broadcast\PiSignage\Models\Playlist;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;
use Storage;

/**
 * This job synchronises locations in the Network DB with the Display Units in BroadSign. New Display Units are added,
 * old ones are removed, and others gets updated as needed. Each ActorsLocations is associated of format, and its location in
 * the containers tree in BroadSign is carried on to the Network DB.
 *
 * @package Neo\Jobs
 */
class CreateSchedule extends PiSignageJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $scheduleId;

    public function uniqueId(): int {
        return $this->scheduleId;
    }

    public function __construct(PiSignageConfig $config, int $scheduleId) {
        parent::__construct($config);
        $this->scheduleId = $scheduleId;
    }

    public function handle(): void {
        // In PiSignage, Schedules have equivalent representation. Scheduling dates and times are instead stored in assets, meaning assets have to be imported for each schedule that they belong to

        /** @var Schedule $schedule */
        $schedule = Schedule::query()->find($this->scheduleId);

        if(!$schedule) {
            // Schedule doesn't exist
            return;
        }

        $creatives = $schedule->content->creatives;

        /** @var Creative $creative */
        foreach ($creatives as $creative) {
            /** @var Asset $asset */
            $asset = Asset::makeStatic($this->getAPIClient(), $schedule->id . "@" . $creative->id, Storage::get($creative->properties->file_path));

            $asset->validity = [
                "enable" => true,
                  "startdate" => $schedule->start_date->toDateString(),
                  "enddate" => $schedule->end_date->toDateString(),
                  "starthour" => $schedule->start_date->hour,
                  "endhour" => $schedule->end_date->hour
            ];

            $asset->save();
        }
    }

}
