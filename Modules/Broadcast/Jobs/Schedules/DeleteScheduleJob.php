<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DeleteScheduleJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Schedules;

use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Jobs\BroadcastJobBase;
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Services\ExternalCampaignDefinition;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @extends BroadcastJobBase<array{representation: ExternalCampaignDefinition|null}>
 */
class DeleteScheduleJob extends BroadcastJobBase {
    /**
     * @param int                             $scheduleId
     * @param ExternalCampaignDefinition|null $representation Specific representation to work with
     * @param BroadcastJob|null               $broadcastJob
     */
    public function __construct(int $scheduleId, ExternalCampaignDefinition|null $representation = null, BroadcastJob|null $broadcastJob = null) {
        parent::__construct(BroadcastJobType::DeleteSchedule, $scheduleId, ["representation" => $representation], $broadcastJob);
    }

    /**
     * Steps here:
     * 1. List all formats in the campaign the schedule fits in;
     * 2. For each representation:
     *   2A. List external ids for the schedule
     *   2B. Update the schedule if ids exist, or create it
     *      2Ba. If the schedule does not already exist, list the content's creatives external id
     *      2Bb. if a creative has no external ID for the broadcaster, import it
     *      2Bc. Create the schedule
     *   2C. Update/Create/Delete the schedule external ids to match the new state
     * 3. Return the list of external ID of the schedule for the current state
     *
     * @inheritDoc
     * @return array|null
     * @throws UnknownProperties
     * @throws InvalidBroadcasterAdapterException
     */
    protected function run(): array|null {
        // A schedule has a content which in turn fits in a layout
        // A layout can be present in multiple formats
        // We list all the formats the schedule content's layout fit in,
        // and only keep the campaign representation that match with this list
        /** @var Schedule $schedule */
        $schedule = Schedule::withTrashed()->find($this->resourceId);
        $schedule->load(["campaign", "external_representations", "content", "content.layout"]);
        $formatsIds = $schedule->content->layout->formats()->allRelatedIds();

        $campaignRepresentations = [];

        // If a specific representation is given, use this one, otherwise list all the representation of the campaign
        if (!is_null($this->payload["representation"])) {
            $campaignRepresentations[] = $this->payload["representation"];
        } else {
            // For each representation of the campaign, we dispatch an update and a targeting action
            $campaignRepresentations = $schedule->campaign->getExternalBreakdown();
        }

        // Filter the campaign's representations to only keep the one matching the formats with which the content's layout is associated
        $scheduleRepresentations = array_filter($campaignRepresentations, static fn(ExternalCampaignDefinition $representation) => $formatsIds->contains($representation->format_id));

        // This array will hold updated resources, and errors
        $results = [];

        /** @var ExternalCampaignDefinition $representation */
        foreach ($scheduleRepresentations as $representation) {
            /** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
            $broadcaster = BroadcasterAdapterFactory::makeForNetwork($representation->network_id);

            if (!$broadcaster->hasCapability(BroadcasterCapability::Scheduling)) {
                // This broadcaster does not handle content scheduling
                continue;
            }

            // Get the external ID representation for this schedule
            /** @var array<ExternalResource> $externalResources */
            $externalResources = $schedule->getExternalRepresentation($broadcaster->getBroadcasterId(), $representation->network_id, $representation->format_id);

            $broadcaster->deleteSchedule(externalResources: array_map(static fn(ExternalResource $resource) => $resource->toResource(), $externalResources));

            foreach ($externalResources as $externalResource) {
                $externalResource->delete();
            }

            $results[] = $externalResources;
        }

        return $results;
    }
}
