<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PromoteCampaignJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Campaigns;

use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Exceptions\ExternalBroadcastResourceNotFoundException;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Jobs\BroadcastJobBase;
use Neo\Modules\Broadcast\Jobs\Schedules\DeleteScheduleJob;
use Neo\Modules\Broadcast\Jobs\Schedules\PromoteScheduleJob;
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Frame;
use Neo\Modules\Broadcast\Models\Layout;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Models\StructuredColumns\ExternalResourceData;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Services\Exceptions\CannotUpdateExternalResourceException;
use Neo\Modules\Broadcast\Services\ExternalCampaignDefinition;
use Neo\Modules\Broadcast\Services\Resources\CampaignTargeting;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Broadcast\Utils\BroadcastTagsCollector;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @extends BroadcastJobBase<array>
 */
class PromoteCampaignJob extends BroadcastJobBase {
    public function __construct(int $campaignId, BroadcastJob|null $broadcastJob = null) {
        parent::__construct(BroadcastJobType::PromoteCampaign, $campaignId, null, $broadcastJob);
    }

    /**
     * @inheritDoc
     * @return array|null
     * @throws UnknownProperties
     * @throws InvalidBroadcasterAdapterException
     */
    protected function run(): array|null {
        // For each representation of the campaign, we dispatch an update and a targeting action
        /** @var Campaign $campaign */
        $campaign = Campaign::withTrashed()->find($this->resourceId);

        if ($campaign->trashed()) {
            // The campaign is trashed, we don't want to schedule anything for it
            return [
                "error"   => true,
                "message" => "Campaign is trashed.",
            ];
        }

        $campaignRepresentations = $campaign->getExternalBreakdown();
        $updatedResources        = [];
        $hasErrors               = false;

        /** @var ExternalCampaignDefinition $representation */
        foreach ($campaignRepresentations as $representation) {
            /** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
            $broadcaster = BroadcasterAdapterFactory::makeForNetwork($representation->network_id);

            if (!$broadcaster->hasCapability(BroadcasterCapability::Scheduling)) {
                // This broadcaster does not handle content scheduling
                continue;
            }

            /** @var Format $format */
            $format = Format::query()
                            ->with(["broadcast_tags",
                                    "layouts",
                                    "layouts.broadcast_tags",
                                    "layouts.broadcast_tags.external_representations",
                                    "layouts.frames",
                                    "layouts.frames.broadcast_tags.external_representations",
                                    "loop_configurations"])
                            ->find($representation->format_id);

            // Get the campaign resource and complete it
            $campaignResource                = $campaign->toResource();
            $campaignResource->name          .= " - " . $format->name;
            $campaignResource->duration_msec = $format->content_length * 1000;

            // Get the external ID for this campaign representation
            $externalResource = $campaign->getExternalRepresentation($broadcaster->getBroadcasterId(), $broadcaster->getNetworkId(), $format->getKey());

            // If no external resource could be found, it means the external campaign for this representation does not exist, create it.
            /** @var ExternalBroadcasterResourceId|null $externalCampaignId */
            $externalCampaignId = null;
            $createCampaign     = !$externalResource;

            if ($externalResource) {
                // This flag will let us know if the campaign schedules needs to be rescheduled
                try {
                    // There is an external campaign for this representation, update it
                    $externalCampaignId = $broadcaster->updateCampaign($externalResource->toResource(), $campaignResource);
                } catch (ExternalBroadcastResourceNotFoundException) {
                    // External Resource could not be found, it may have been deleted from the broadcaster directly. Delete our reference to it, and create it
                    $externalResource->delete();
                    $createCampaign = true;
                } catch (CannotUpdateExternalResourceException) {
                    // Campaign could not be updated. Course of action here is to delete the external campaign and all its schedule, and recreate everything
                    $broadcaster->deleteCampaign($externalResource->toResource());
                    $externalResource->delete();
                    $createCampaign = true;
                } finally {
                    if ($createCampaign) {
                        // If we want to create the campaign, that means we had to remove the existing one, or we couldn't find the one we had in store.
                        // To prevent leaving dangling resources on the broadcaster, we will also remove all the schedules attached to this representation
                        $schedules = $campaign->schedules;

                        /** @var Schedule $schedule */
                        foreach ($schedules as $schedule) {
                            $deleteScheduleJob = new DeleteScheduleJob($schedule->getKey(), $representation);
                            $deleteScheduleJob->handle();
                        }
                    }
                }
            }

            if ($createCampaign) {
                // No external ID found for this representation, create it
                $externalCampaignId = $broadcaster->createCampaign($campaignResource);
            }

            // If at this point we don't have an external campaign id. it means something bad happened.
            // Stop here and throw
            if (!$externalCampaignId) {
                $updatedResources = [
                    "error"      => true,
                    "message"    => "Could not promote campaign",
                    "network_id" => $representation->network_id,
                    "format_id"  => $representation->format_id,
                ];
                $hasErrors        = true;
                continue;
            }

            // If we just created the campaign, or the update returned a new ID, we have to register it
            if ($externalCampaignId->external_id !== $externalResource?->data->external_id) {
                // Delete previous resource if it exist
                $externalResource?->delete();

                $externalResource = new ExternalResource([
                    "resource_id"    => $campaign->getKey(),
                    "broadcaster_id" => $broadcaster->getBroadcasterId(),
                    "type"           => ExternalResourceType::Campaign,
                    "data"           => new ExternalResourceData([
                        "network_id"  => $broadcaster->getNetworkId(),
                        "formats_id"  => [$format->getKey()],
                        "external_id" => $externalCampaignId->external_id,
                    ]),
                ]);
                $externalResource->save();
            }

            // If we just created a campaign, trigger a promotion for the schedules in the campaign
            // for the current representation. If we just recreated the campaign, the schedules have been deleted and need
            // to be recreated.
            if ($createCampaign) {
                $schedules = $campaign->schedules;

                /** @var Schedule $schedule */
                foreach ($schedules as $schedule) {
                    $deleteScheduleJob = new PromoteScheduleJob($schedule->getKey(), $representation);
                    $deleteScheduleJob->handle();
                }
            }

            // Now that the campaign exist, we need to target it
            // List all tags relevant to the campaign, and dispatch the action
            $campaignTags  = new BroadcastTagsCollector();
            $locationsTags = new BroadcastTagsCollector();

            // Format tags
            $campaignTags->collect($format->broadcast_tags, [BroadcastTagType::Targeting]);

            // Layout tags
            $campaignTags->collect($format->layouts->flatMap(fn(Layout $layout) => $layout->broadcast_tags), [BroadcastTagType::Targeting]);

            // Frames tags
            $framesTags = $format->layouts->flatMap(fn(Layout $layout) => $layout->frames->flatMap(fn(Frame $frame) => $frame->broadcast_tags));
            $campaignTags->collect($framesTags, [BroadcastTagType::Targeting]);
            $locationsTags->collect($framesTags, [BroadcastTagType::Targeting]);

            // Campaign
            $campaignTags->collect($campaign->broadcast_tags, [BroadcastTagType::Targeting]);

            // Target the campaign
            $targeting = new CampaignTargeting([
                "campaignTags"  => $campaignTags->get($broadcaster->getBroadcasterId()),
                "locations"     => $representation->locations,
                "locationsTags" => $locationsTags->get($broadcaster->getBroadcasterId()),
            ]);

            $broadcaster->targetCampaign($externalCampaignId, $targeting);

            $updatedResources[] = $externalResource;
        }

        if (!$hasErrors) {
            // No error in script, `$updateResources` holds all the valid external representation for the campaign.
            // If there is other representations attached to the campaign, we remove them
            // List campaign representation not in the list of valid ones
            $validRepresentationsIds = collect($updatedResources)->pluck("id");
            $outdatedRepresentations = $campaign->external_representations->whereNotIn("id", $validRepresentationsIds)
                                                                          ->whereNull("deleted_at");

            foreach ($outdatedRepresentations as $outdatedRepresentation) {
                /** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
                $broadcaster = BroadcasterAdapterFactory::makeForNetwork($outdatedRepresentation->data->network_id);

                if (!$broadcaster->hasCapability(BroadcasterCapability::Scheduling)) {
                    // This broadcaster does not handle content scheduling
                    continue;
                }

                $broadcaster->deleteCampaign($outdatedRepresentation->toResource());
                $outdatedRepresentation->delete();
            }
        }

        return $updatedResources;
    }
}
