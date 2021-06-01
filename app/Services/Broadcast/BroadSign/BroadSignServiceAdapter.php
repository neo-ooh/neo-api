<?php

namespace Neo\Services\Broadcast\BroadSign;

use Illuminate\Support\Collection;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\BroadcastService;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
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
use Neo\Services\Broadcast\BroadSign\Jobs\SynchronizePlayers;
use Neo\Services\Broadcast\BroadSign\Models\Campaign;
use Neo\Services\Broadcast\BroadSign\Models\Customer;

class BroadSignServiceAdapter implements BroadcastService {

    protected BroadSignConfig $config;

    public function __construct(BroadSignConfig $config) {
        $this->config = $config;
    }

    public function getConfig(): BroadSignConfig {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    public function synchronizeLocations() {
        // TODO: switch `dispatchSync` to `dispatch` once migration 2021_05_20_145217_assign_campaigns_network has run
        SynchronizeLocations::dispatchSync($this->config);
    }

    /**
     * @inheritDoc
     */
    public function synchronizePlayers() {
        // TODO: switch `dispatchSync` to `dispatch` once migration 2021_05_20_145217_assign_campaigns_network has run
        SynchronizePlayers::dispatchSync($this->config);
    }

    /**
     * @inheritDoc
     */
    public function destroyCreative(string $creative_external_id) {
        DisableBroadSignCreative::dispatch($this->config, (int)$creative_external_id);
    }

    /**
     * @inheritDoc
     */
    public function createSchedule(int $scheduleId, int $actorIdId) {
        CreateBroadSignSchedule::dispatchSync($this->config, $scheduleId, $actorIdId);
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
        DisableBroadSignSchedule::dispatch($this->config, (int)Schedule::find($scheduleId)->external_id_2);
    }

    /**
     * @param array $query
     * @return Collection
     */
    public function searchCampaigns(array $query): Collection {
        return Campaign::search(new BroadsignClient($this->config), $query);
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
    public function destroyCampaign(int $campaignId) {
        DisableBroadSignCampaign::dispatch($this->config, (int)\Neo\Models\Campaign::query()->find($campaignId)->external_id);
    }

    /**
     * @inheritDoc
     */
    public function rebuildCampaign(int $campaignId) {
        RebuildBroadSignCampaign::dispatch($this->config, $campaignId);
    }

    public function listCustomers(): Collection {
        return ;
    }
}
