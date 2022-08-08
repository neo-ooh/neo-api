<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UpdateGroup.php
 */

namespace Neo\Services\Broadcast\PiSignage\Jobs\Groups;


use DateTimeZone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Services\PiSignage\Models\Group;
use Neo\Modules\Broadcast\Services\PiSignage\PiSignageConfig;
use Neo\Services\Broadcast\PiSignage\Jobs\PiSignageJob;

/**
 * @package Neo\Jobs
 */
class UpdateGroup extends PiSignageJob implements ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function uniqueId(): int {
        return $this->locationId;
    }

    public function __construct(PiSignageConfig $config, protected int $locationId) {
        parent::__construct($config);
    }

    public function handle(): void {
        /** @var ?Location $location */
        $location = Location::query()->find($this->locationId);

        if (!$location) {
            return;
        }

        /** @var \Neo\Modules\Broadcast\Services\PiSignage\Models\Group $group */
        $group = Group::get($this->getAPIClient(), $location->external_id);

        if ($location->scheduled_sleep) {
            $group->sleep = [
                "enable"     => true,
                "ontime"     => $location->sleep_end->toTimeString('minute'),
                "offtime"    => $location->sleep_start->toTimeString('minute'),
                "ontimeObj"  => $location->sleep_end->shiftTimezone(new DateTimeZone("America/Toronto"))->toISOString(true),
                "offtimeObj" => $location->sleep_start->shiftTimezone(new DateTimeZone("America/Toronto"))->toISOString(true),
            ];
        } else {
            $group->sleep = ["enable" => false];
        }

        $group->deploy = true;
        $group->save();
    }
}
