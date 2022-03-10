<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PiSignageServiceAdapter.php
 */

namespace Neo\Services\Broadcast\PiSignage;

use Neo\Models\Schedule;
use Neo\Services\Broadcast\BroadcastService;
use Neo\Services\Broadcast\PiSignage\Jobs\Campaigns\CreateCampaign;
use Neo\Services\Broadcast\PiSignage\Jobs\Campaigns\DestroyCampaign;
use Neo\Services\Broadcast\PiSignage\Jobs\Campaigns\SetCampaignSchedules;
use Neo\Services\Broadcast\PiSignage\Jobs\Campaigns\TargetCampaign;
use Neo\Services\Broadcast\PiSignage\Jobs\Groups\UpdateGroup;
use Neo\Services\Broadcast\PiSignage\Jobs\Players\TurnPlayerTVOnOff;
use Neo\Services\Broadcast\PiSignage\Jobs\Schedules\CreateSchedule;
use Neo\Services\Broadcast\PiSignage\Jobs\Schedules\DestroySchedule;
use Neo\Services\Broadcast\PiSignage\Jobs\Schedules\UpdateSchedule;
use Neo\Services\Broadcast\PiSignage\Jobs\SynchronizeLocations;
use Neo\Services\Broadcast\PiSignage\Jobs\SynchronizePlayers;

class PiSignageServiceAdapter implements BroadcastService {

    public PiSignageConfig $config;

    public function __construct(PiSignageConfig $config) {
        $this->config = $config;
    }

    public function getConfig(): PiSignageConfig {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function synchronizeLocations() {
        SynchronizeLocations::dispatch($this->config);
    }

    /**
     * @inheritDoc
     */
    public function synchronizePlayers() {
        SynchronizePlayers::dispatch($this->config);

    }

    /**
     * @inheritDoc
     */
    public function createSchedule(int $scheduleId, int $actorId) {
        CreateSchedule::dispatch($this->config, $scheduleId);
    }

    /**
     * @inheritDoc
     */
    public function updateSchedule(int $scheduleId) {
        UpdateSchedule::dispatch($this->config, $scheduleId);
    }

    /**
     * @inheritDoc
     */
    public function enableSchedule(int $scheduleId) {
        SetCampaignSchedules::dispatch($this->config, Schedule::query()->find($scheduleId)->campaign_id);
    }

    /**
     * @inheritDoc
     */
    public function disableSchedule(int $scheduleId) {
        SetCampaignSchedules::dispatch($this->config, Schedule::query()->find($scheduleId)->campaign_id);
    }

    /**
     * @inheritDoc
     */
    public function destroySchedule(int $scheduleId) {
        // This job has tot run synchronously has we need the relation between the schedule and its creatives to know which one to remove
        DestroySchedule::dispatchSync($this->config, $scheduleId);
    }

    /**
     * @inheritDoc
     */
    public function createCampaign(int $campaignId) {
        CreateCampaign::dispatch($this->config, $campaignId);
    }

    /**
     * @inheritDoc
     */
    public function updateCampaign(int $campaignId) {
        TargetCampaign::dispatch($this->config, $campaignId);
    }

    /**
     * @inheritDoc
     */
    public function targetCampaign(int $campaignId) {
        TargetCampaign::dispatchSync($this->config, $campaignId);
    }

    /**
     * @inheritDoc
     */
    public function updateCampaignSchedulesOrder(int $campaignId) {
        SetCampaignSchedules::dispatch($this->config, $campaignId);
    }

    /**
     * @inheritDoc
     */
    public function destroyCampaign(int $campaignId) {
        DestroyCampaign::dispatchSync($this->config, $campaignId);
    }

    /**
     * @inheritDoc
     */
    public function rebuildCampaign(int $campaignId) {
        // TODO: Implement rebuildCampaign() method.
    }

    /**
     * @inheritDoc
     */
    public function destroyCreative(string $external_creative_id) {
        // Method ignored for PiSignage as creatives are tied to schedules and are deleted with them from the server
    }

    public function searchCampaigns(array $query) {
        // TODO: Implement searchCampaigns() method.
    }

    public function setScreenState(string $external_player_id, bool $state) {
        TurnPlayerTVOnOff::dispatchSync($this->config, $external_player_id, $state);
    }

    public function updateLocation(string $location_id) {
        UpdateGroup::dispatchSync($this->config, $location_id);
    }
}
