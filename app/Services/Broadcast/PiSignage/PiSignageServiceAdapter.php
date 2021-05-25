<?php

namespace Neo\Services\Broadcast\PiSignage;

use Neo\Models\Network;
use Neo\Services\Broadcast\BroadcastService;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\PiSignage\Jobs\Campaigns\CreateCampaign;
use Neo\Services\Broadcast\PiSignage\Jobs\SynchronizeLocations;

class PiSignageServiceAdapter implements BroadcastService {

    public PiSignageConfig $config;

    public function __construct(PiSignageConfig $config) {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function synchronizeLocations() {
        SynchronizeLocations::dispatchSync($this->config);
    }

    /**
     * @inheritDoc
     */
    public function synchronizePlayers() {
        // TODO: Implement synchronizePlayers() method.
    }

    /**
     * @inheritDoc
     */
    public function createSchedule(int $scheduleId, int $actorId) {
        // TODO: Implement createSchedule() method.
    }

    /**
     * @inheritDoc
     */
    public function updateSchedule(int $scheduleId) {
        // TODO: Implement updateSchedule() method.
    }

    /**
     * @inheritDoc
     */
    public function enableSchedule(int $scheduleId) {
        // TODO: Implement enableSchedule() method.
    }

    /**
     * @inheritDoc
     */
    public function disableSchedule(int $scheduleId) {
        // TODO: Implement disableSchedule() method.
    }

    /**
     * @inheritDoc
     */
    public function destroySchedule(string $schedule_external_id) {
        // TODO: Implement destroySchedule() method.
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
        // TODO: Implement updateCampaign() method.
    }

    /**
     * @inheritDoc
     */
    public function targetCampaign(int $campaignId) {
        // TODO: Implement targetCampaign() method.
    }

    /**
     * @inheritDoc
     */
    public function updateCampaignSchedulesOrder(int $campaignId) {
        // TODO: Implement updateCampaignSchedulesOrder() method.
    }

    /**
     * @inheritDoc
     */
    public function destroyCampaign(string $campaign_external_id) {
        // TODO: Implement destroyCampaign() method.
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
        // TODO: Implement destroyCreative() method.
    }

    public function searchCampaigns(array $query) {
        // TODO: Implement searchCampaigns() method.
    }
}
