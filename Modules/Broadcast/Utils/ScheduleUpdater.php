<?php

namespace Neo\Modules\Broadcast\Utils;

use Carbon\Carbon;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleBroadcastDaysException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleDatesException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleTimesException;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Schedule;

class ScheduleUpdater {
    protected Schedule|null $schedule = null;
    protected Campaign|null $campaign = null;

    /**
     * @param Schedule $schedule
     * @return ScheduleUpdater
     */
    public function setSchedule(Schedule $schedule): self {
        $this->schedule = $schedule;
        return $this;
    }

    /**
     * @param Campaign $campaign
     * @return ScheduleUpdater
     */
    public function setCampaign(Campaign $campaign): self {
        $this->campaign = $campaign;
        return $this;
    }

    /**
     * @throws InvalidScheduleTimesException
     * @throws InvalidScheduleDatesException
     * @throws InvalidScheduleBroadcastDaysException
     */
    public function update(Carbon $startDate, Carbon $startTime, Carbon $endDate, Carbon $endTime, int $weekdays, bool $forceFit = false) {
        $validator = new ScheduleValidator();

        if ($forceFit) {
            [$this->schedule->start_date,
             $this->schedule->start_time,
             $this->schedule->end_date,
             $this->schedule->end_time,
             $this->schedule->broadcast_days,
            ] = $validator->forceFitSchedulingInCampaign(
                campaign: $this->campaign,
                startDate: $startDate,
                startTime: $startTime,
                endDate: $endDate,
                endTime: $endTime,
                weekdays: $weekdays
            );
        } else {
            $validator->validateSchedulingFitCampaign(
                campaign: $this->campaign,
                startDate: $startDate,
                startTime: $startTime,
                endDate: $endDate,
                endTime: $endTime,
                weekdays: $weekdays
            );

            $this->schedule->start_date     = $startDate;
            $this->schedule->start_time     = $startTime;
            $this->schedule->end_date       = $endDate;
            $this->schedule->end_time       = $endTime;
            $this->schedule->broadcast_days = $weekdays;
        }

        $this->schedule->save();
    }
}
