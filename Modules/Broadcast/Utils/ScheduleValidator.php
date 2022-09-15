<?php

namespace Neo\Modules\Broadcast\Utils;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Neo\Modules\Broadcast\Exceptions\CannotScheduleContentAnymoreException;
use Neo\Modules\Broadcast\Exceptions\CannotScheduleIncompleteContentException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentFormatAndCampaignException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentLengthAndCampaignException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleBroadcastDaysException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleDatesException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleTimesException;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Format;

class ScheduleValidator {
    /**
     * This function validates that the content can be inserted in the campaign
     * Any error triggers an exception
     *
     * @throws IncompatibleContentFormatAndCampaignException
     * @throws IncompatibleContentLengthAndCampaignException
     * @throws CannotScheduleIncompleteContentException
     * @throws CannotScheduleContentAnymoreException
     */
    public function validateContentFitCampaign(Content $content, Campaign $campaign): void {
        // Is the filled ?
        if ($content->layout->frames->count() !== $content->creatives()->count()) {
            throw new CannotScheduleIncompleteContentException();
        }

        // List campaign formats that support the content's layout
        $campaignFormats = $campaign->formats()
                                    ->with("loop_configurations")
                                    ->whereHas("layouts", function (Builder $query) use ($content) {
                                        $query->where("id", "=", $content->layout_id);
                                    })
                                    ->get();

        // Does the schedule has the correct format
        if ($campaignFormats->count() === 0) {
            throw new IncompatibleContentFormatAndCampaignException();
        }

        // Confirm we are within the maximum allowed number of schedule limit for this content
        $scheduleCount = $content->schedules()->where("is_locked", "-", true)->count();
        if ($content->max_schedule_count !== 0 && $scheduleCount >= $content->max_schedule_count) {
            throw new CannotScheduleContentAnymoreException();
        }

        // make sure the content length match all the fitting formats
        $formatLengths = $campaignFormats->flatMap(fn(Format $format) => $format->loop_configurations->pluck("spot_length_ms"))
                                         ->unique();
        if ($content->duration > 0 && array_any($formatLengths, fn(int $length) => ($content->duration * 1000) > $length)) {
            throw new IncompatibleContentLengthAndCampaignException();
        }
    }

    /**
     * @param Campaign $campaign
     * @param Carbon   $startDate
     * @param Carbon   $startTime
     * @param Carbon   $endDate
     * @param Carbon   $endTime
     * @param int      $weekdays
     * @return void
     * @throws InvalidScheduleBroadcastDaysException
     * @throws InvalidScheduleDatesException
     * @throws InvalidScheduleTimesException
     */
    public function validateSchedulingFitCampaign(Campaign $campaign, Carbon $startDate, Carbon $startTime, Carbon $endDate, Carbon $endTime, int $weekdays): void {
        if ($startDate->isAfter($endDate) ||
            $startDate->isBefore($campaign->start_date) ||
            $endDate->isAfter($campaign->end_date)) {
            throw new InvalidScheduleDatesException();
        }

        if ($startTime->isAfter($endTime) ||
            $endTime->isBefore($campaign->start_time) ||
            $endTime->isAfter($campaign->end_time)) {
            throw new InvalidScheduleTimesException();
        }

        if (($weekdays & $campaign->broadcast_days) !== $weekdays) {
            throw new InvalidScheduleBroadcastDaysException();
        }
    }
}
