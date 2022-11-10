<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
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
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @extends BroadcastJobBase<array>
 */
class DeleteCampaignJob extends BroadcastJobBase {
    public function __construct(int $campaignId, BroadcastJob|null $broadcastJob = null) {
        parent::__construct(BroadcastJobType::DeleteCampaign, $campaignId, null, $broadcastJob);
    }

    /**
     * @inheritDoc
     * @return array|null
     * @throws InvalidBroadcasterAdapterException
     * @throws UnknownProperties
     */
    protected function run(): array|null {
        /** @var Campaign $campaign */
        $campaign = Campaign::withTrashed()->find($this->resourceId);

        // For each representation of the campaign, we remove it from its broadcaster, and mark it as deleted
        $externalResources = $campaign->external_representations->whereNull("deleted_at");
        $results           = [];

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
            $results[] = $externalResource;
        }

        return $results;
    }
}
