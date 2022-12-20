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
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcastResource;
use Neo\Modules\Broadcast\Jobs\BroadcastJobBase;
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Services\ExternalCampaignDefinition;

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
     * @throws InvalidBroadcasterAdapterException|InvalidBroadcastResource
     */
    protected function run(): array|null {
        // Load the schedule
        /** @var Schedule $schedule */
        $schedule = Schedule::withTrashed()->find($this->resourceId);
        $schedule->load(["campaign", "external_representations", "contents", "contents.layout"]);

        // If a representation is given, we remove this one only
        if ($representation = $this->payload["representation"]) {
            return $this->removeRepresentation($schedule, $representation->network_id, $representation->format_id);
        }

        // No representation given, list all the external resources of the schedule, group them by representation (network+format), and remove them
        $representations = $schedule->external_representations->groupBy(fn(ExternalResource $resource) => "{$resource->data->network_id}-{$resource->data->formats_id[0]}");

        // This array will hold updated resources, and errors
        $results = [];

        foreach ($representations as $externalResources) {
            /** @var ExternalResource $resource */
            $resource  = $externalResources->first();
            $networkId = $resource->data->network_id;
            $formatId  = $resource->data->formats_id[0];

            $results[] = $this->removeRepresentation($schedule, $networkId, $formatId);
        }

        return $results;
    }

    /**
     * @param Schedule $schedule
     * @param int      $networkId
     * @param int      $formatId
     * @return ExternalResource[]
     * @throws InvalidBroadcastResource
     * @throws InvalidBroadcasterAdapterException
     */
    protected function removeRepresentation(Schedule $schedule, int $networkId, int $formatId): array {
        /** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
        $broadcaster = BroadcasterAdapterFactory::makeForNetwork($networkId);

        if (!$broadcaster->hasCapability(BroadcasterCapability::Scheduling)) {
            // This broadcaster does not handle content scheduling
            return [];
        }

        // Get the external ID representation for this schedule
        /** @var array<ExternalResource> $externalResources */
        $externalResources = $schedule->getExternalRepresentation($broadcaster->getBroadcasterId(), $networkId, $formatId);

        $broadcaster->deleteSchedule(externalResources: array_map(static fn(ExternalResource $resource) => $resource->toResource(), $externalResources));

        foreach ($externalResources as $externalResource) {
            $externalResource->delete();
        }

        return $externalResources;
    }
}
