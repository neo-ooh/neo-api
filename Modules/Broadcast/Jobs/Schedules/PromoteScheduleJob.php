<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PromoteScheduleJob.php
 */

namespace Neo\Modules\Broadcast\Jobs\Schedules;

use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Enums\BroadcastJobType;
use Neo\Modules\Broadcast\Enums\BroadcastParameters;
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Exceptions\CouldNotPromoteResourceException;
use Neo\Modules\Broadcast\Exceptions\ExternalBroadcastResourceNotFoundException;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcastResource;
use Neo\Modules\Broadcast\Exceptions\MissingExternalCreativeException;
use Neo\Modules\Broadcast\Jobs\BroadcastJobBase;
use Neo\Modules\Broadcast\Jobs\Creatives\ImportCreativeJob;
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Creative;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Models\StructuredColumns\ExternalResourceData;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Services\Exceptions\MissingExternalResourceException;
use Neo\Modules\Broadcast\Services\ExternalCampaignDefinition;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Broadcast\Services\Resources\Schedule as ScheduleResource;
use Neo\Modules\Broadcast\Services\Resources\Tag;
use Neo\Modules\Broadcast\Utils\BroadcastTagsCollector;

/**
 * @extends BroadcastJobBase<array{representation: ExternalCampaignDefinition|null}>
 */
class PromoteScheduleJob extends BroadcastJobBase {
	public const TYPE = BroadcastJobType::PromoteSchedule;

	/**
	 * @param int                             $scheduleId
	 * @param ExternalCampaignDefinition|null $representation Specific representation to work with
	 * @param BroadcastJob|null               $broadcastJob
	 */
	public function __construct(int $scheduleId, ExternalCampaignDefinition|null $representation = null, BroadcastJob|null $broadcastJob = null) {
		parent::__construct(static::TYPE, $scheduleId, ["representation" => $representation], $broadcastJob);
	}

	/**
	 * Steps here:
	 * 1. List all formats/representations of the campaign the schedule fits in;
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
	 * @throws InvalidBroadcasterAdapterException
	 * @throws InvalidBroadcastResource
	 * @throws MissingExternalCreativeException
	 * @throws CouldNotPromoteResourceException
	 */
	protected function run(): array|null {
		// A schedule has one or more contents which in turn fits in a layout.
		// A layout can be present in multiple formats
		// We list all the formats the schedule's contents' layouts fit in,
		// and only keep the campaign representation that match with this list
		/** @var Schedule $schedule */
		$schedule = Schedule::withTrashed()->find($this->resourceId);

		if (!$schedule) {
			return [
				"error"   => true,
				"message" => "Schedule could not be found",
			];
		}

		if ($schedule->trashed()) {
			return [
				"error"   => true,
				"message" => "Schedule is trashed",
			];
		}

		$schedule->load([
			                "campaign",
			                "external_representations",
			                "contents.layout.formats",
			                "contents.schedule_settings.disabled_formats_ids",
		                ]);
		$formatsIds = $schedule->contents->flatMap(fn(Content $content) => $content->layout->formats)->pluck("id")->unique();

		$useSpecificRepresentation = !is_null($this->payload["representation"]);
		$campaignRepresentations   = [];

		$lengthThresholdSec = param(BroadcastParameters::CreativeLengthFlexibilitySec);

		// If a specific representation is given, use this one, otherwise list all the representation of the campaign
		if ($useSpecificRepresentation) {
			$campaignRepresentations[] = $this->payload["representation"];
		} else {
			$campaignRepresentations = $schedule->campaign->getExternalBreakdown();
		}

		// Filter the representations to only keep the ones matching the formats within which the contents' layouts are associated
		$scheduleRepresentations = array_filter($campaignRepresentations, static fn(ExternalCampaignDefinition $representation) => $formatsIds->contains($representation->format_id));

		if (count($scheduleRepresentations) === 0) {
			// Since no representation could be found, we cannot do anything.
			// If a specific campaign definition was given, we do want to perform a cleanup on it though.
			if ($useSpecificRepresentation) {
				$deleteJob = new DeleteScheduleJob($this->resourceId, $campaignRepresentations[0]);
				$deleteJob->handle();
			} else {
				$this->cleanUpExternalRepresentations($schedule, []);
			}

			return [
				"error"                    => true,
				"message"                  => "No representation of campaign matching schedule found",
				"campaign_representations" => array_map(static fn(ExternalCampaignDefinition $definition) => $definition, $campaignRepresentations),
			];
		}

		// This array will hold newly created resources, and errors
		$results   = [];
		$hasErrors = false;

		// Iterate over all the selected representations
		/** @var ExternalCampaignDefinition $representation */
		foreach ($scheduleRepresentations as $representation) {
			/** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
			$broadcaster = BroadcasterAdapterFactory::makeForNetwork($representation->network_id);

			if (!$broadcaster->hasCapability(BroadcasterCapability::Scheduling)) {
				// This broadcaster does not handle content scheduling, ignore
				continue;
			}

			/** @var Format $representationFormat */
			$representationFormat      = Format::query()->find($representation->format_id);
			$representationMaxDuration = $schedule->campaign->dynamic_duration_override > PHP_FLOAT_EPSILON
				? $schedule->campaign->dynamic_duration_override
				: $representationFormat->content_length;

			// Get the external ID for this campaign representation
			/** @var ExternalResource|null $externalCampaignResource */
			$externalCampaignResource = $schedule->campaign->getExternalRepresentation($broadcaster->getBroadcasterId(), $representation->network_id, $representation->format_id);

			if (!$externalCampaignResource) {
				// The campaign has no ID for this representation.
				// The campaign may have not been replicated yet, or has failed its scheduling.
				// We want to retry later for this specific representation, but only if we are not specialized
				if ($useSpecificRepresentation) {
					throw new CouldNotPromoteResourceException($broadcaster, $this->resourceId, [
						"message"    => "Missing External Representation for campaign",
						"network_id" => $representation->network_id,
						"format_id"  => $representation->format_id,
					]);
				}

				// Scheduled a promote job specific to the current representation so we can try again later.
				new PromoteScheduleJob($this->resourceId, $representation);

				continue;
			}

			// List which contents from the schedule match the current definition. For a content
			// to be included, its layout must be attached to the definition's format, and the definition's format must not be part of the list of excluded formats of the content for this schedule.
			/** @var Collection<Content> $contents */
			$contents = $schedule->contents->filter(function (Content $content) use ($lengthThresholdSec, $representation, $representationMaxDuration) {
				// Validate content length is within representation limit
				if ($content->duration >= $representationMaxDuration + $lengthThresholdSec) {
					return false;
				}

				// Validate one of the content formats matches the campaign
				if ($content->layout->formats->pluck("id")->doesntContain($representation->format_id)) {
					return false;
				}

				// Validate the representation format has not been disabled for this content
				if ($content->schedule_settings->disabled_formats_ids->pluck("format_id")->contains($representation->format_id)) {
					return false;
				}

				return true;
			});

			// Get the required resources
			$layouts          = $representationFormat->layouts()->whereIn("layouts.id", $contents->pluck("layout_id"))->get();
			$scheduleResource = $schedule->toResource();

			// Complete the schedule resource
			// If at least one of the content has a set duration, we add the proper duration to the resource]\
			if ($schedule->campaign->dynamic_duration_override > PHP_FLOAT_EPSILON && $contents->max("duration") > PHP_FLOAT_EPSILON) {
				$scheduleResource->duration_msec = (int)round($schedule->campaign->dynamic_duration_override * 1000);
			} else {
				$scheduleResource->duration_msec = (int)round(($schedule->campaign->static_duration_override ?: $representationFormat->content_length) * 1000);
			}

			// If all the layouts in this format are fullscreen, mark the bundle as such
			$scheduleResource->is_fullscreen = $layouts->every("settings.is_fullscreen", "=", true);

			// Get the external ID representation for this schedule
			/** @var array<ExternalResource> $externalResources Existing external representations at the start of the job */
			$externalResources = $schedule->getExternalRepresentation($broadcaster->getBroadcasterId(), $representation->network_id, $representation->format_id);

			// Collect all tags for the schedule
			$tags = new BroadcastTagsCollector();
			// Schedule tags
			$tags->collect($schedule->broadcast_tags, [BroadcastTagType::Category]);
			// Campaign tags
			$tags->collect($schedule->campaign->broadcast_tags, [BroadcastTagType::Category]);
			// Format tags
			$tags->collect($representationFormat->broadcast_tags, [BroadcastTagType::Category]);
			// Layout tags
			$tags->collect($schedule->contents->pluck("layout.broadcast_tags")
			                                  ->flatten(), [BroadcastTagType::Category, BroadcastTagType::Trigger]);
			// Content tags
			$tags->collect($schedule->contents->pluck("broadcast_tags")->flatten(), [BroadcastTagType::Category]);

			$scheduleTags = $tags->get($broadcaster->getBroadcasterId());
			if (count($externalResources) === 0) {
				// If no external resource could be found, it means the external schedule for this representation does not exist, create it.
				// Create the schedule in the broadcaster
				$updatedExternalResources = $this->createSchedule(
					broadcaster             : $broadcaster,
					scheduleResource        : $scheduleResource,
					externalCampaignResource: $externalCampaignResource,
					tags                    : $scheduleTags,
				);
			} else {
				// We have ids for this schedule, try to update it
				try {
					// There is an external schedule for this representation, update it
					$updatedExternalResources = $broadcaster->updateSchedule(
						externalResources: array_map(static fn(ExternalResource $r) => $r->toResource(), $externalResources),
						schedule         : $scheduleResource,
						tags             : $scheduleTags,
					);
				} catch (ExternalBroadcastResourceNotFoundException|MissingExternalResourceException $err) {
					// The broadcaster did not find any schedule with the id provided. Try to create it instead
					$updatedExternalResources = $this->createSchedule(
						broadcaster             : $broadcaster,
						scheduleResource        : $scheduleResource,
						externalCampaignResource: $externalCampaignResource,
						tags                    : $scheduleTags,
					);
				}
			}

			if (count($updatedExternalResources) === 0) {
				throw new CouldNotPromoteResourceException($broadcaster, $this->resourceId, [
					"message"                    => "No external ids could be obtained or created for schedule",
					"broadcaster_id"             => $broadcaster->getBroadcasterId(),
					"network_id"                 => $representation->network_id,
					"format_id"                  => $representation->format_id,
					"updated_external_resources" => $updatedExternalResources,
				]);
			}

			// We now have IDs to our resources, check if some have changed, and replace them if necessary
			/** @var ExternalBroadcasterResourceId $updatedExternalResource */
			foreach ($updatedExternalResources as $updatedExternalResource) {
				/** @var ExternalResource[] $sameTypeResources */
				$sameTypeResources = array_filter($externalResources, static function (ExternalResource $externalResource) use ($updatedExternalResource) {
					return $externalResource->type === $updatedExternalResource->type;
				});

				// Remove any resource that doesn't match the updated ID
				/** @var ExternalResource[] $validResources */
				$validResources = [];

				foreach ($sameTypeResources as $resource) {
					if ($resource->data->external_id === $updatedExternalResource->external_id) {
						$validResources[] = $resource;
						continue;
					}

					$resource->delete();
				}

				// Is there at least one resource that matches the new one ?
				if (count($validResources) === 0) {
					// No, store the new one
					$newExternalResource = new ExternalResource([
						                                            "resource_id"    => $schedule->getKey(),
						                                            "broadcaster_id" => $broadcaster->getBroadcasterId(),
						                                            "type"           => $updatedExternalResource->type,
						                                            "data"           => new ExternalResourceData(
							                                            external_id: $updatedExternalResource->external_id,
							                                            network_id : $broadcaster->getNetworkId(),
							                                            formats_id : [$representation->format_id],
						                                            ),
					                                            ]);
					$newExternalResource->save();

					$results[] = $newExternalResource;
				} else {
					// The updated resource is already reference, just add it to the results
					array_push($results, ...$validResources);
				}
			}

			// Finally, attach/sync the creatives with the schedules
			$this->attachContentsToSchedules($broadcaster, $updatedExternalResources, $contents);
		}

		// If we are working with all the representations, and no errors occured,
		// we do a cleanup of the external resources attached to the schedule, removing
		// all resources that are not present in the results list
		if (!$useSpecificRepresentation && !$hasErrors) {
			$validRepresentationsIds = collect($results)->pluck("id");
			$this->cleanUpExternalRepresentations($schedule, $validRepresentationsIds->all());
		}

		return $results;
	}

	/**
	 * @param BroadcasterOperator&BroadcasterScheduling $broadcaster
	 * @param ScheduleResource                          $scheduleResource
	 * @param ExternalResource                          $externalCampaignResource
	 * @param array<Tag>                                $tags
	 * @return array<ExternalBroadcasterResourceId>
	 */
	protected function createSchedule(
		BroadcasterOperator&BroadcasterScheduling $broadcaster,
		ScheduleResource                          $scheduleResource,
		ExternalResource                          $externalCampaignResource,
		array                                     $tags
	): array {
		// Now that we have all the creatives Ids, create the schedule
		return $broadcaster->createSchedule(
			schedule: $scheduleResource,
			campaign: $externalCampaignResource->toResource(),
			tags    : $tags);
	}

	/**
	 * Takes a list of contents, the representations for a schedule, and properly attach the contents to the schedule
	 * using the given broadcaster.
	 *
	 * @param BroadcasterOperator&BroadcasterScheduling $broadcaster
	 * @param ExternalBroadcasterResourceId[]           $externalRepresentations
	 * @param Collection<Content>                       $contents
	 * @return void
	 * @throws MissingExternalCreativeException
	 */
	public function attachContentsToSchedules(BroadcasterOperator&BroadcasterScheduling $broadcaster, array $externalRepresentations, Collection $contents): void {
		// To create a schedule, we need to make sure all the creatives attached to its content have been imported in the broadcaster
		$creativesExternalIds = [];

		foreach ($contents as $content) {
			// For each creative, we need to check if it exists in the current broadcaster, and if not, import it
			$creatives = $content->creatives;

			/** @var Creative $creative */
			foreach ($creatives as $creative) {
				$creativesExternalId = $this->getCreativeExternalId($broadcaster, $creative);

				if (is_null($creativesExternalId)) {
					throw new MissingExternalCreativeException($broadcaster, $creative);
				}

				$creativesExternalIds[] = $creativesExternalId;
			}
		}

		$broadcaster->setScheduleContents($externalRepresentations, $creativesExternalIds);
	}

	/**
	 * give the external ID of a creative for the given broadcaster.
	 * If the creative doesn't exist in the given broadcaster, we will try to import it
	 */
	protected function getCreativeExternalId(BroadcasterOperator $broadcaster, Creative $creative): ExternalBroadcasterResourceId|null {
		$externalCreative = $creative->getExternalRepresentation($broadcaster->getBroadcasterId());

		if ($externalCreative) {
			return $externalCreative->toResource();
		}

		$importCreativeJob = new ImportCreativeJob($creative->getKey(), $broadcaster->getBroadcasterId());
		$importCreativeJob->handle();

		$result = $importCreativeJob->getLastAttemptResult();
		dump($result);
		return $result;
	}

	/**
	 * Takes a list of valid `ExternalResource` models ids, and properly remove other external resources still attached
	 * to the schedules, removing them from their broadcasters as well.
	 *
	 * @param Schedule $schedule
	 * @param int[]    $validRepresentationsIds
	 * @return void
	 * @throws InvalidBroadcasterAdapterException
	 * @throws InvalidBroadcastResource
	 */
	protected function cleanUpExternalRepresentations(Schedule $schedule, array $validRepresentationsIds): void {
		// Group external resources by definition (network+format)
		$outdatedRepresentations = $schedule->external_representations
			->whereNotIn("id", $validRepresentationsIds)
			->whereNull("deleted_at")
			->groupBy(fn(ExternalResource $resource) => "{$resource->data->network_id}-{$resource->data->formats_id[0]}");

		// For each outdated representation, remove them from their broadcaster, and mark them as removed
		foreach ($outdatedRepresentations as $externalResources) {
			/** @var ExternalResource $resource */
			$resource  = $externalResources->first();
			$networkId = $resource->data->network_id;
			$formatId  = $resource->data->formats_id[0];

			/** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
			$broadcaster = BroadcasterAdapterFactory::makeForNetwork($networkId);

			if (!$broadcaster->hasCapability(BroadcasterCapability::Scheduling)) {
				// This broadcaster does not handle content scheduling
				continue;
			}

			// Get the external ID representation for this schedule
			/** @var array<ExternalResource> $externalResources */
			$externalResources = $schedule->getExternalRepresentation($broadcaster->getBroadcasterId(), $networkId, $formatId);

			$broadcaster->deleteSchedule(externalResources: array_map(static fn(ExternalResource $resource) => $resource->toResource(), $externalResources));

			foreach ($externalResources as $externalResource) {
				$externalResource->delete();
			}
		}
	}
}
