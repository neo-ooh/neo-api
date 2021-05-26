<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SynchronizeLocations.php
 */

namespace Neo\Services\Broadcast\PiSignage\Jobs\Creatives;


use GuzzleHttp\Psr7\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Creative;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;
use Neo\Services\Broadcast\PiSignage\Models\Asset;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;

/**
 * @package Neo\Jobs
 */
class AssignCreativeValidity extends PiSignageJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $creativeId;
    protected int $scheduleId;

    public function uniqueId(): int {
        return $this->creativeId;
    }

    public function __construct(PiSignageConfig $config, int $creativeId, int $scheduleId) {
        parent::__construct($config);
        $this->creativeId = $creativeId;
        $this->scheduleId = $scheduleId;
    }

    public function handle(): void {
        // In PiSignage, Schedules have equivalent representation. Scheduling dates and times are instead stored in assets, meaning assets have to be imported for each schedule that they belong to

        /** @var Creative $creative */
        $creative = Creative::query()->find($this->creativeId);

        /** @var Schedule $schedule */
        $schedule = Schedule::query()->find($this->scheduleId);

        if (!$creative || !$schedule) {
            // Schedule doesn't exist
            return;
        }

        $assetName = $schedule->id . "@" . $creative->id . "." . $creative->properties->extension;
        $asset = Asset::get($this->getAPIClient(), ["name" => $assetName]);

        if(!$asset->dbdata) {
            // Asset has no dbdata, release and try again later
            $this->release(60);
            return;
        }

        $asset->dbdata["validity"] = [
            "enable"    => true,
            "startdate" => $schedule->start_date->setTime(0, 0)->toISOString(),
            "enddate"   => $schedule->end_date->setTime(0, 0)->toISOString(),
            "starthour" => $schedule->start_date->hour,
            "endhour"   => $schedule->end_date->hour
        ];

        $asset->save();
    }
}
