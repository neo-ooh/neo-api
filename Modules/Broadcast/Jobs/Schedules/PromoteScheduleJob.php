<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PromoteScheduleJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Schedules;

use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Exceptions\ExternalBroadcastResourceNotFoundException;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Exceptions\MissingExternalCreativeException;
use Neo\Modules\Broadcast\Jobs\BroadcastJobBase;
use Neo\Modules\Broadcast\Jobs\Creatives\ImportCreativeJob;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Layout;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Models\StructuredColumns\ExternalResourceData;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Services\ExternalCampaignDefinition;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Broadcast\Services\Resources\Schedule as ScheduleResource;
use Neo\Modules\Broadcast\Services\Resources\Tag;
use Neo\Modules\Broadcast\Utils\BroadcastTagsCollector;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @extends BroadcastJobBase<array{representation: ExternalCampaignDefinition|null}>
 */
class PromoteScheduleJob extends BroadcastJobBase {
    /**
     * @param int                             $scheduleId
     * @param ExternalCampaignDefinition|null $representation Specific representation to work with
     */
    public function __construct(int $scheduleId, ExternalCampaignDefinition|null $representation = null) {
        parent::__construct(BroadcastJobType::PromoteSchedule, $scheduleId, ["representation" => $representation]);
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

        if ($schedule->trashed()) {
            return [
                "error"   => true,
                "message" => "Schedule is trashed"
            ];
        }

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

        // This array will hold newly created resources, and errors
        $results = [];

        /** @var ExternalCampaignDefinition $representation */
        foreach ($scheduleRepresentations as $representation) {
            // Does this schedule fits in this campaign representation ?
            /** @var Format $representationFormat */
            $representationFormat = Format::query()->find($representation->format_id);

            if ($representationFormat->layouts()->allRelatedIds()->doesntContain($schedule->content->layout_id)) {
                // The schedule's content does not fit in this campaign representation, ignore it
                continue;
            }

            /** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
            $broadcaster = BroadcasterAdapterFactory::makeForNetwork($representation->network_id);

            if (!$broadcaster->hasCapability(BroadcasterCapability::Scheduling)) {
                // This broadcaster does not handle content scheduling
                continue;
            }

            // Get the external ID for this campaign representation
            /** @var ExternalResource|null $externalCampaignResource */
            $externalCampaignResource = $schedule->campaign->getExternalRepresentation($broadcaster->getBroadcasterId(), $representation->format_id, $representation->format_id);

            if (!$externalCampaignResource) {
                // The campaign has no ID for this representation. This means the campaign is in an erroneous state
                $results[] = [
                    "error"      => true,
                    "message"    => "Missing External Representation for campaign",
                    "network_id" => $representation->network_id,
                    "format_id"  => $representation->format_id,
                ];

                continue;
            }

            // Get the external ID representation for this schedule
            /** @var array<ExternalResource> $externalResources */
            $externalResources = $schedule->getExternalRepresentation($broadcaster->getBroadcasterId(), $representation->format_id, $representation->format_id);

            $scheduleResource = $schedule->toResource();

            // Collect all tags for the schedule
            $tags = new BroadcastTagsCollector();
            // Schedule tags
            $tags->collect($schedule->broadcast_tags, [BroadcastTagType::Category]);
            // Campaign tags
            $tags->collect($schedule->campaign->broadcast_tags, [BroadcastTagType::Category]);
            // Layout tags
            $tags->collect($schedule->content->layout->broadcast_tags, [BroadcastTagType::Category, BroadcastTagType::Trigger]);
            // Format tags
            $tags->collect($representationFormat->broadcast_tags, [BroadcastTagType::Category]);

            $scheduleTags = $tags->get($broadcaster->getBroadcasterId());

            try {
                if (count($externalResources) === 0) {
                    // If no external resource could be found, it means the external schedule for this representation does not exist, create it.
                    // Create the schedule in the broadcaster
                    $updatedExternalResources = $this->createSchedule(
                        broadcaster: $broadcaster,
                        scheduleResource: $scheduleResource,
                        externalCampaignResource: $externalCampaignResource,
                        content: $schedule->content,
                        format: $representationFormat,
                        tags: $scheduleTags,
                    );
                } else {
                    // We have ids for this schedule, try to update it
                    try {
                        // There is an external schedule for this representation, update it
                        $updatedExternalResources = $broadcaster->updateSchedule(
                            externalResources: array_map(static fn(ExternalResource $r) => $r->toResource(), $externalResources),
                            schedule: $scheduleResource,
                            tags: $scheduleTags,
                        );
                    } catch (ExternalBroadcastResourceNotFoundException) {
                        // The broadcaster did not find any schedule with the id provided. Try to create it instead
                        $updatedExternalResources = $this->createSchedule(
                            broadcaster: $broadcaster,
                            scheduleResource: $scheduleResource,
                            externalCampaignResource: $externalCampaignResource,
                            content: $schedule->content,
                            format: $representationFormat,
                            tags: $scheduleTags,
                        );
                    }
                }
            } catch (MissingExternalCreativeException $e) {
                // There was a problem readying up the creatives for the schedule, register the error and move along
                $results[] = [
                    "error"          => true,
                    "message"        => "Could not get creatives ready for schedule creation",
                    "broadcaster_id" => $broadcaster->getBroadcasterId(),
                    "trace"          => $e->getTrace(),
                ];

                continue;
            }

            // We now have IDs to our resources, check if some have changed, and replace them if necessary
            /** @var ExternalBroadcasterResourceId $updatedExternalResource */
            foreach ($updatedExternalResources as $updatedExternalResource) {
                // For each created resource, find an existing id for it
                /** @var ExternalResource|null $newExternalResource */
                $existingExternalResource = array_filter($externalResources, static fn(ExternalResource $resource) => $resource->type = $updatedExternalResource->type)[0] ?? null;

                // If we have an existing resource for this external resource, check if it needs to be updated
                if (($existingExternalResource?->data->external_id ?? null) !== $updatedExternalResource->external_id) {
                    $existingExternalResource?->delete();

                    $newExternalResource = new ExternalResource([
                        "resource_id"    => $schedule->getKey(),
                        "broadcaster_id" => $broadcaster->getBroadcasterId(),
                        "type"           => $updatedExternalResource->type,
                        "data"           => new ExternalResourceData([
                            "network_id"  => $broadcaster->getNetworkId(),
                            "formats_id"  => [$representation->format_id],
                            "external_id" => $updatedExternalResource->external_id,
                        ])
                    ]);
                    $newExternalResource->save();

                    $results[] = $newExternalResource;
                } else if ($existingExternalResource !== null) {
                    $results[] = $existingExternalResource;
                }
            }
        }

        return $results;
    }

    /**
     * @param BroadcasterOperator&BroadcasterScheduling $broadcaster
     * @param ScheduleResource                          $scheduleResource
     * @param ExternalResource                          $externalCampaignResource
     * @param Content                                   $content
     * @param Format                                    $format
     * @param array<Tag>                                $tags
     * @return array<ExternalBroadcasterResourceId>
     * @throws MissingExternalCreativeException
     * @throws UnknownProperties
     */
    protected function createSchedule(BroadcasterOperator&BroadcasterScheduling $broadcaster,
                                      ScheduleResource                          $scheduleResource,
                                      ExternalResource                          $externalCampaignResource,
                                      Content                                   $content,
                                      Format                                    $format,
                                      array                                     $tags): array {
        // To create a schedule, we need to make sure all the creative attached to its content have been imported in the broadcaster
        // For each creative, we need to check if it exist in the current broadcaster, and if not, import it
        $creatives            = $content->creatives;
        $creativesExternalIds = [];

        /** @var Creative $creative */
        foreach ($creatives as $creative) {
            $creativesExternalId = $this->getCreativeExternalId($broadcaster, $creative);

            if (is_null($creativesExternalId)) {
                throw new MissingExternalCreativeException($broadcaster, $creative);
            }

            $creativesExternalIds[] = $creativesExternalId;
        }

        // Also, we need to get the `is_fullscreen` attribute for the layout in the format
        /** @var Layout $layout */
        $layout = $format->layouts()->where("layout_id", "=", $content->layout_id)->first();

        $contentResource                = $content->toResource();
        $contentResource->is_fullscreen = $layout->settings->is_fullscreen;

        // Now that we have all the creatives Ids, create the schedule
        return $broadcaster->createSchedule(
            schedule: $scheduleResource,
            campaign: $externalCampaignResource->toResource(),
            content: $contentResource,
            creatives: $creativesExternalIds,
            tags: $tags);
    }

    /**
     * give the external ID of a creative for the given broadcaster.
     * If the creative doesn't exist in the given broadcaster, we will try to import it
     *
     * @throws UnknownProperties
     */
    protected function getCreativeExternalId(BroadcasterOperator $broadcaster, Creative $creative): ExternalBroadcasterResourceId|null {
        $externalCreative = $creative->getExternalRepresentation($broadcaster->getBroadcasterId());

        if ($externalCreative) {
            return $externalCreative->toResource();
        }

        $importCreativeJob = new ImportCreativeJob($creative->getKey(), $broadcaster->getBroadcasterId());
        $importCreativeJob->handle();
        return $importCreativeJob->getLastAttemptResult();
    }
}
