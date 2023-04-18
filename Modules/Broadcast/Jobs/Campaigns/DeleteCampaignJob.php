<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DeleteCampaignJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Campaigns;

use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Jobs\BroadcastJobBase;
use Neo\Modules\Broadcast\Jobs\Schedules\DeleteScheduleJob;
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Services\ExternalCampaignDefinition;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

/**
 * @extends BroadcastJobBase<array{resource_id: int|null}>
 */
class DeleteCampaignJob extends BroadcastJobBase {
    public function __construct(int $campaignId, int|null $resourceId = null, BroadcastJob|null $broadcastJob = null) {
        parent::__construct(BroadcastJobType::DeleteCampaign, $campaignId, ["resource_id" => $resourceId], $broadcastJob);
    }

    /**
     * @inheritDoc
     * @return array|null
     * @throws InvalidBroadcasterAdapterException
     */
    protected function run(): array|null {
        /** @var Campaign $campaign */
        $campaign = Campaign::withTrashed()->find($this->resourceId);

        // For each representation of the campaign, we remove it from its broadcaster, and mark it as deleted
        if (is_array($this->payload) && $this->payload["resource_id"]) {
            $externalResource  = ExternalResource::query()->find($this->payload["resource_id"]);
            $externalResources = $externalResource !== null ? [$externalResource] : [];
        } else {
            $externalResources = $campaign->external_representations->whereNull("deleted_at");
        }

        $results = [];

        /** @var ExternalResource $externalResource */
        foreach ($externalResources as $externalResource) {
            /** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
            $broadcaster = BroadcasterAdapterFactory::makeForNetwork($externalResource->data->network_id);

            if (!$broadcaster->hasCapability(BroadcasterCapability::Scheduling)) {
                // This broadcaster does not handle content scheduling
                continue;
            }

            $broadcaster->deleteCampaign(externalCampaign: $externalResource->toResource());

            $externalResource->delete();

            // We want to trigger removal of schedules
            foreach ($campaign->schedules as $schedule) {
                foreach (($externalResource->data->formats_id ?? []) as $formatId) {
                    $campaignDefinition = new ExternalCampaignDefinition(
                        campaign_id: $campaign->getKey(),
                        network_id : $broadcaster->getNetworkId(),
                        format_id  : $formatId,
                        locations  : ExternalBroadcasterResourceId::collection([]),
                    );

                    $deleteScheduleJob = new DeleteScheduleJob($schedule->getKey(), $campaignDefinition);
                    $deleteScheduleJob->handle();
                }
            }

            $results[] = $externalResource;
        }

        return $results;
    }
}
