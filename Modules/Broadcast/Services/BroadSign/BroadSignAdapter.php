<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignAdapter.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign;

use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\Resources\Campaign;
use Neo\Modules\Broadcast\Services\Resources\Creative;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcastResourceId;
use Neo\Modules\Broadcast\Services\Resources\Schedule;
use Neo\Modules\Broadcast\Services\ResourcesComparator;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Models\Campaign as BroadSignCampaign;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class BroadSignAdapter implements BroadcasterOperator {
    public function __construct(protected BroadSignConfig $config) {
    }

    protected function getAPIClient(): BroadsignClient {
        return new BroadsignClient($this->config);
    }

    /**
     * @throws UnknownProperties
     */
    public function createCampaign(Campaign $campaign): ExternalBroadcastResourceId {
        $bsCampaign = new BroadSignCampaign($this->getAPIClient(), [
            "name" => $campaign->name,

            "duration_msec" => $campaign->default_schedule_length_msec,

            "start_date"       => $campaign->start_date,
            "start_time"       => $campaign->start_time,
            "end_date"         => $campaign->end_date,
            "end_time"         => $campaign->end_time,
            "day_of_week_mask" => $campaign->broadcast_days,

            "priority"   => $campaign->priority,
            "saturation" => $campaign->occurrences_in_loop,

            "auto_synchronize_bundles" => true,
            "container_id"             => $this->config->reservationsContainerId,
            "parent_id"                => $this->config->customerId,
        ]);

        $bsCampaign->create();

        return new ExternalBroadcastResourceId(external_id: $bsCampaign->getKey());
    }

    /**
     * @throws UnknownProperties
     */
    public function checkCampaign(ExternalBroadcastResourceId $externalCampaign, Campaign $expected): ResourcesComparator {
        $bsCampaign = BroadSignCampaign::get($this->getAPIClient(), $externalCampaign->external_id);

        return new ResourcesComparator($expected, $bsCampaign->toResource());
    }

    /**
     * @throws UnknownProperties
     */
    public function updateCampaign(ExternalBroadcastResourceId $externalCampaign, Campaign $campaign): ExternalBroadcastResourceId {
        $bsCampaign = BroadSignCampaign::get($this->getAPIClient(), $externalCampaign->external_id);

        $comparator = new ResourcesComparator($campaign, $bsCampaign->toResource());

        // It is not possible to change some of the properties of a campaign after it has been created.
        $updatable          = true;
        $readonlyProperties = ["start_date", "start_time", "end_date", "end_time", "saturation", "default_schedule_length_msec"];

        foreach ($readonlyProperties as $property) {
            if ($comparator->isDifferent($property)) {
                $updatable = false;
                break;
            }
        }

        if (!$updatable) {
            $this->deleteCampaign($externalCampaign);


            // TODO: Campaign targeting ?
            return $this->createCampaign($campaign);
        }

        $bsCampaign->name             = $campaign->name;
        $bsCampaign->duration_msec    = $campaign->default_schedule_length_msec;
        $bsCampaign->start_date       = $campaign->start_date;
        $bsCampaign->start_time       = $campaign->start_time;
        $bsCampaign->end_date         = $campaign->end_date;
        $bsCampaign->end_time         = $campaign->end_time;
        $bsCampaign->day_of_week_mask = $campaign->broadcast_days;
        $bsCampaign->priority         = $campaign->priority;
        $bsCampaign->saturation       = $campaign->occurrences_in_loop;
        $bsCampaign->save();

        return new ExternalBroadcastResourceId(external_id: $bsCampaign->getKey());
    }

    public function deleteCampaign(ExternalBroadcastResourceId $externalCampaign): bool {
        $bsCampaign = BroadSignCampaign::get($this->getAPIClient(), $externalCampaign->external_id);

        if (!$bsCampaign) {
            return false;
        }

        $bsCampaign->active = false;
        $bsCampaign->state  = $bsCampaign->state === BroadSignReservationState::HeldCancelled->value ? BroadSignReservationState::HeldCancelled->value : BroadSignReservationState::Cancelled->value;
        $bsCampaign->save();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function createCreative(Creative $creative): ExternalBroadcastResourceId {
        // TODO: Implement createCreative() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteCreative(ExternalBroadcastResourceId $externalCreative): bool {
        // TODO: Implement deleteCreative() method.
    }

    /**
     * @inheritDoc
     */
    public function createSchedule(Schedule $schedule): ExternalBroadcastResourceId {
        // TODO: Implement createSchedule() method.
    }

    /**
     * @inheritDoc
     */
    public function updateSchedule(ExternalBroadcastResourceId $externalSchedule, Schedule $schedule): ExternalBroadcastResourceId {
        // TODO: Implement updateSchedule() method.
    }

    /**
     * @inheritDoc
     */
    public function destroySchedule(ExternalBroadcastResourceId $externalSchedule): bool {
        // TODO: Implement destroySchedule() method.
    }
}
