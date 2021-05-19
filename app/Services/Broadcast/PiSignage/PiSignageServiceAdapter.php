<?php

namespace Neo\Services\Broadcast\PiSignage;

use Neo\Models\Network;
use Neo\Services\Broadcast\BroadcastService;

class PiSignageServiceAdapter implements BroadcastService {
    protected Network $network;

    public function __construct(Network $network) {
        $this->network = $network;
    }

    /**
     * @inheritDoc
     */
    public function synchronizeLocations() {
        // TODO: Implement synchronizeLocations() method.
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
    public function destroySchedule(int $scheduleId) {
        // TODO: Implement destroySchedule() method.
    }

    /**
     * @inheritDoc
     */
    public function createCampaign(int $campaignId) {
        // TODO: Implement createCampaign() method.
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
    public function destroyCampaign(int $externalId) {
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
    public function destroyCreative(int $externalId) {
        // TODO: Implement destroyCreative() method.
    }
}
