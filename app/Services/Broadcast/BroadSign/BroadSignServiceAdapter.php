<?php

namespace Neo\Services\Broadcast\BroadSign;

use Neo\Services\Broadcast\BroadcastService;
use Neo\Services\Broadcast\BroadSign\Jobs\Campaigns\CreateBroadSignCampaign;
use Neo\Services\Broadcast\BroadSign\Jobs\Campaigns\DisableBroadSignCampaign;
use Neo\Services\Broadcast\BroadSign\Jobs\Campaigns\RebuildBroadSignCampaign;
use Neo\Services\Broadcast\BroadSign\Jobs\Campaigns\TargetCampaign;
use Neo\Services\Broadcast\BroadSign\Jobs\Campaigns\UpdateBroadSignCampaign;
use Neo\Services\Broadcast\BroadSign\Jobs\Creatives\DisableBroadSignCreative;
use Neo\Services\Broadcast\BroadSign\Jobs\Schedules\CreateBroadSignSchedule;
use Neo\Services\Broadcast\BroadSign\Jobs\Schedules\DisableBroadSignSchedule;
use Neo\Services\Broadcast\BroadSign\Jobs\Schedules\ReorderBroadSignSchedules;
use Neo\Services\Broadcast\BroadSign\Jobs\Schedules\UpdateBroadSignSchedule;
use Neo\Services\Broadcast\BroadSign\Jobs\Schedules\UpdateBroadSignScheduleStatus;
use Neo\Services\Broadcast\BroadSign\Jobs\SynchronizeLocations;

class BroadSignServiceAdapter implements BroadcastService {

    protected BroadSignConfig $config;

    public function __construct(BroadSignConfig $config) {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function synchronizeLocations() {
        dump("dispath ". $this->config->connectionUUID);
        SynchronizeLocations::dispatchSync($this->config);
    }

    /**
     * @inheritDoc
     */
    public function destroyCreative(int $externalId) {
        DisableBroadSignCreative::dispatch($this->config, $externalId);
    }

    /**
     * @inheritDoc
     */
    public function createSchedule(int $scheduleId, int $actorIdId) {
        CreateBroadSignSchedule::dispatch($this->config, $scheduleId, $actorIdId);
    }

    /**
     * @inheritDoc
     */
    public function updateSchedule(int $scheduleId) {
        UpdateBroadSignSchedule::dispatch($this->config, $scheduleId);
    }

    /**
     * @inheritDoc
     */
    public function enableSchedule(int $scheduleId) {
        UpdateBroadSignScheduleStatus::dispatch($this->config, $scheduleId);
    }

    /**
     * @inheritDoc
     */
    public function disableSchedule(int $scheduleId) {
        UpdateBroadSignScheduleStatus::dispatch($this->config, $scheduleId);
    }

    /**
     * @inheritDoc
     */
    public function destroySchedule(int $scheduleId) {
        DisableBroadSignSchedule::dispatch($this->config, $scheduleId);
    }

    /**
     * @inheritDoc
     */
    public function createCampaign(int $campaignId) {
        CreateBroadSignCampaign::dispatch($this->config, $campaignId);
    }

    /**
     * @inheritDoc
     */
    public function updateCampaign(int $campaignId) {
        UpdateBroadSignCampaign::dispatch($this->config, $campaignId);
    }

    /**
     * @inheritDoc
     */
    public function targetCampaign(int $campaignId) {
        TargetCampaign::dispatch($this->config, $campaignId);
    }

    /**
     * @inheritDoc
     */
    public function updateCampaignSchedulesOrder(int $campaignId) {
        ReorderBroadSignSchedules::dispatch($this->config, $campaignId);
    }

    /**
     * @inheritDoc
     */
    public function destroyCampaign(int $externalId) {
        DisableBroadSignCampaign::dispatch($this->config, $externalId);
    }

    /**
     * @inheritDoc
     */
    public function rebuildCampaign(int $campaignId) {
        RebuildBroadSignCampaign::dispatch($this->config, $campaignId);
    }
}
