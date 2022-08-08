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
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Services\ExternalCampaignDefinition;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @extends BroadcastJobBase<array>
 */
class DeleteCampaignJob extends BroadcastJobBase {
    public function __construct(int $campaignId) {
        parent::__construct(BroadcastJobType::DeleteCampaign, $campaignId);
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

        // For each representation of the campaign, we dispatch an update and a targeting action
        $campaignRepresentations  = $campaign->getExternalBreakdown();
        $deletedExternalResources = [];

        /** @var ExternalCampaignDefinition $representation */
        foreach ($campaignRepresentations as $representation) {
            /** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
            $broadcaster = BroadcasterAdapterFactory::makeForNetwork($representation->network_id);

            if (!$broadcaster->hasCapability(BroadcasterCapability::Scheduling)) {
                // This broadcaster does not handle content scheduling
                continue;
            }

            // Get the external ID for this campaign representation
            /** @var ExternalResource|null $externalResource */
            $externalResource = $campaign->getExternalRepresentation($broadcaster->getBroadcasterId(), $broadcaster->getNetworkId(), $representation->format_id);

            if (!$externalResource) {
                // No resource available for this representation
                continue;
            }

            $broadcaster->deleteCampaign($externalResource->toResource());

            $externalResource->delete();
            $deletedExternalResources[] = $externalResource;
        }

        return $deletedExternalResources;
    }
}
