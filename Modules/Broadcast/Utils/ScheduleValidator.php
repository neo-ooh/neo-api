<?php

namespace Neo\Modules\Broadcast\Utils;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Enums\BroadcastParameters;
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
        // Is the content filled ?
        if ($content->layout->frames->count() !== $content->creatives()->count()) {
            throw new CannotScheduleIncompleteContentException();
        }

        // List campaign formats that support the content's layout
        /** @var Collection<Format> $campaignFormats */
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

        // If the content length is not zero, we make sure its length match at least one of the format
        if ($content->duration > 0) {
            /** @var float $lengthThresholdSec */
            $lengthThresholdSec = param(BroadcastParameters::CreativeLengthFlexibilitySec);

            // If the campaign has a dynamic override, this takes priority over the formats, check that first
            if ($campaign->dynamic_duration_override > PHP_FLOAT_EPSILON && $content->duration > ($campaign->dynamic_duration_override + $lengthThresholdSec)) {
                throw new IncompatibleContentLengthAndCampaignException();
            }

            // If the campaign has no duration override, check that the content matches at least one format.
            if ($campaign->dynamic_duration_override <= PHP_FLOAT_EPSILON) {
                $validFormats = $campaignFormats->filter(fn(Format $format) => $content->duration < ($format->content_length + $lengthThresholdSec));

                if ($validFormats->isEmpty()) {
                    throw new IncompatibleContentLengthAndCampaignException();
                }
            }
        }
    }

    /**
     * Validates the provided dates, times and weekdays can fit in the given campaign
     *
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

    /**
     * Returns the given dates, times and days adjusted to fit in the campaign.
     * If the provided date, time or days ranges do not overlap those of the campaign, errors will be thrown.
     *
     * @param Campaign $campaign
     * @param Carbon   $startDate
     * @param Carbon   $startTime
     * @param Carbon   $endDate
     * @param Carbon   $endTime
     * @param int      $weekdays
     * @return array Array containing the fixed value in the same order as they were passed : [startDate, startTime, endDate,
     *               endTime, weekdays]
     * @throws InvalidScheduleBroadcastDaysException
     * @throws InvalidScheduleDatesException
     * @throws InvalidScheduleTimesException
     */
    public function forceFitSchedulingInCampaign(Campaign $campaign, Carbon $startDate, Carbon $startTime, Carbon $endDate, Carbon $endTime, int $weekdays): array {
        // Make sure the provided dates and the campaign's dates overlap
        if ($startDate->isAfter($endDate) ||
            $startDate->isAfter($campaign->end_date) ||
            $endDate->isBefore($campaign->start_date)) {
            throw new InvalidScheduleDatesException();
        }

        $fixedStartDate = $startDate->isBefore($campaign->start_date) ? $campaign->start_date->clone() : $startDate->clone();
        $fixedEndDate   = $endDate->isAfter($campaign->end_date) ? $campaign->end_date->clone() : $endDate->clone();

        if ($startTime->isAfter($endTime) ||
            $startTime->isAfter($campaign->end_time) ||
            $endTime->isBefore($campaign->start_time)) {
            throw new InvalidScheduleTimesException();
        }

        $fixedStartTime = $startTime->isBefore($campaign->start_time) ? $campaign->start_time->clone() : $startTime->clone();
        $fixedEndTime   = $endTime->isAfter($campaign->end_time) ? $campaign->end_time->clone() : $endTime->clone();

        if (($weekdays & $campaign->broadcast_days) === 0) {
            throw new InvalidScheduleBroadcastDaysException();
        }

        $fixedWeekdays = $weekdays & $campaign->broadcast_days;

        return [$fixedStartDate, $fixedStartTime, $fixedEndDate, $fixedEndTime, $fixedWeekdays];
    }
}
