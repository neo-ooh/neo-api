<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DeleteExpiredResourcesJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Chores;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Neo\Jobs\Job;
use Neo\Modules\Broadcast\Jobs\Campaigns\DeleteCampaignJob;
use Neo\Modules\Broadcast\Jobs\Creatives\DeleteCreativeJob;
use Neo\Modules\Broadcast\Jobs\Schedules\DeleteScheduleJob;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\Schedule;

class DeleteExpiredResourcesJob extends Job {
    /**
     * @param int $offset Days. The number of days in the past to look into for expired schedule.
     *                    If the offset is 4, a campaign who expired two days ago will be processed,
     *                    a campaign who expired 5 days ago will be ignored.
     */
    public function __construct(protected int $offset = 1) {
    }

    public function run(): mixed {
        $firstBound  = Carbon::now()->subDays($this->offset)->toDateString();
        $secondBound = Carbon::now()->toDateString();

        // List campaigns that have expired
        /** @var Collection<Campaign> $expiredCampaigns */
        $expiredCampaigns = Campaign::withTrashed()
                                    ->where("end_date", ">=", $firstBound)
                                    ->where("end_date", "<=", $secondBound);

        foreach ($expiredCampaigns as $expiredCampaign) {
            $deleteCampaignJob = new DeleteCampaignJob($expiredCampaign->getKey());
            $deleteCampaignJob->handle();
        }

        // List schedules that have expired
        /** @var Collection<Schedule> $expiredSchedules */
        $expiredSchedules = Schedule::withTrashed()
                                    ->where("end_date", ">=", $firstBound)
                                    ->where("end_date", "<=", $secondBound);

        foreach ($expiredSchedules as $expiredSchedule) {
            $deleteScheduleJob = new DeleteScheduleJob($expiredSchedule->getKey());
            $deleteScheduleJob->handle();
        }

        // List creatives that have expired
        // A creative is considered expired if one of its scheduled expired in the checked window, and it does not have any other schedule ending after today
        /** @var Collection<Creative> $expiredCreatives */
        $expiredCreatives = Creative::query()
                                    ->with(["content.schedules", "external_ids"])
                                    ->whereDoesntHave("content.schedules", function (Builder $query) use ($secondBound) {
                                        $query->where("end_date", ">", $secondBound);
                                    })
                                    ->whereHas("content.schedules", function (Builder $query) use ($secondBound, $firstBound) {
                                        $query->where("end_date", ">=", $firstBound);
                                        $query->where("end_date", "<=", $secondBound);
                                    })->lazy(100);

        foreach ($expiredCreatives as $expiredCreative) {
            $deleteCreativeJob = new DeleteCreativeJob($expiredCreative->getKey());
            $deleteCreativeJob->handle();
        }

        return null;
    }
}
