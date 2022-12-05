<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignAdapter.php
 */

namespace Neo\Modules\Broadcast\Services\BroadSign;

use Generator;
use Illuminate\Support\Collection;
use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Exceptions\ExternalBroadcastResourceNotFoundException;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcastResource;
use Neo\Modules\Broadcast\Exceptions\UnsupportedBroadcasterFunctionalityException;
use Neo\Modules\Broadcast\Models\BroadcasterConnection;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterConfig;
use Neo\Modules\Broadcast\Services\BroadcasterContainers;
use Neo\Modules\Broadcast\Services\BroadcasterLocations;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterReporting;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Services\BroadcasterScreenshotsBurst;
use Neo\Modules\Broadcast\Services\BroadcasterUtils;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Bundle as BroadSignBundle;
use Neo\Modules\Broadcast\Services\BroadSign\Models\BundleContent;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Campaign as BroadSignCampaign;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Container as BroadSignContainer;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Creative as BroadSignCreative;
use Neo\Modules\Broadcast\Services\BroadSign\Models\DayPart;
use Neo\Modules\Broadcast\Services\BroadSign\Models\DisplayType as BroadSignDisplayType;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Location as BroadSignLocation;
use Neo\Modules\Broadcast\Services\BroadSign\Models\LoopSlot;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Player as BroadSignPlayer;
use Neo\Modules\Broadcast\Services\BroadSign\Models\ReservablePerformance;
use Neo\Modules\Broadcast\Services\BroadSign\Models\ResourceCriteria;
use Neo\Modules\Broadcast\Services\BroadSign\Models\ResourceQuery;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Schedule as BroadSignSchedule;
use Neo\Modules\Broadcast\Services\BroadSign\Models\Skin;
use Neo\Modules\Broadcast\Services\Exceptions\BroadcastServiceException;
use Neo\Modules\Broadcast\Services\Exceptions\CannotUpdateExternalResourceException;
use Neo\Modules\Broadcast\Services\Exceptions\InvalidExternalBroadcasterResourceType;
use Neo\Modules\Broadcast\Services\Exceptions\MissingExternalResourceException;
use Neo\Modules\Broadcast\Services\Resources\ActiveHours;
use Neo\Modules\Broadcast\Services\Resources\Campaign;
use Neo\Modules\Broadcast\Services\Resources\CampaignSearchResult;
use Neo\Modules\Broadcast\Services\Resources\CampaignTargeting;
use Neo\Modules\Broadcast\Services\Resources\Container;
use Neo\Modules\Broadcast\Services\Resources\Creative;
use Neo\Modules\Broadcast\Services\Resources\CreativeStorageType;
use Neo\Modules\Broadcast\Services\Resources\DisplayType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Broadcast\Services\Resources\Frame;
use Neo\Modules\Broadcast\Services\Resources\Location;
use Neo\Modules\Broadcast\Services\Resources\Player;
use Neo\Modules\Broadcast\Services\Resources\Schedule;
use Neo\Modules\Broadcast\Services\Resources\Tag;
use Neo\Modules\Broadcast\Services\ResourcesComparator;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Traversable;

/**
 * @extends BroadcasterOperator<BroadSignConfig>
 */
class BroadSignAdapter extends BroadcasterOperator implements
    BroadcasterLocations,
    BroadcasterScheduling,
    BroadcasterReporting,
    BroadcasterContainers,
    BroadcasterScreenshotsBurst {

    protected array $capabilities = [
        BroadcasterCapability::Locations,
        BroadcasterCapability::Scheduling,
        BroadcasterCapability::Reporting,
        BroadcasterCapability::ScreenshotsBurst,
        BroadcasterCapability::Containers,
    ];

    public static function buildConfig(BroadcasterConnection $connection, Network $network): BroadcasterConfig {
        $config                          = new BroadSignConfig();
        $config->name                    = $connection->name;
        $config->connectionID            = $network->broadcaster_connection->id;
        $config->connectionUUID          = $network->broadcaster_connection->uuid;
        $config->networkID               = $network->id;
        $config->networkUUID             = $network->uuid;
        $config->apiURL                  = config("modules-legacy.broadsign.api-url");
        $config->domainId                = $connection->settings->domain_id;
        $config->adCopiesContainerId     = $network->settings->creatives_container_id;
        $config->customerId              = $network->settings->customer_id ?? $connection->settings->customer_id;
        $config->containerId             = $network->settings->root_container_id;
        $config->reservationsContainerId = $network->settings->campaigns_container_id;

        return $config;
    }

    protected function getAPIClient(): BroadSignClient {
        return new BroadSignClient($this->config);
    }

    /**
     * @throws UnknownProperties
     */
    public function listLocations(): Traversable {
        return $this->parseContainer($this->config->containerId);
    }

    /**
     * @param int  $containerId
     * @param bool $recursive
     * @return Generator<Location>
     * @throws UnknownProperties
     */
    protected function parseContainer(int $containerId, bool $recursive = true): Generator {
        $bsLocations = BroadSignLocation::inContainer($this->getAPIClient(), $containerId);

        foreach ($bsLocations as $bsLocation) {
            yield $bsLocation->toResource();
        }

        // Are we parsing recursively ?
        if ($recursive) {
            // List the containers in the current one
            $containers = BroadSignContainer::inContainer($this->getAPIClient(), $containerId);

            foreach ($containers as $container) {
                // We want to make sure we are not getting a container that is not the child of the current one, or is the current one.
                // This is to prevent infinite loops.
                if ($container->id === $containerId || $container->container_id !== $containerId) {
                    continue;
                }

                // Parse child container and yield all its locations
                yield from $this->parseContainer($container->id);
            }
        }
    }

    /**
     * @return iterable<Player>
     * @throws UnknownProperties
     */
    public function listPlayers(): iterable {
        /** @var Collection<BroadSignPlayer> $allPlayers */
        $allPlayers = BroadSignPlayer::all($this->getAPIClient());

        return array_map(static fn(BroadSignPlayer $resource) => $resource->toResource(), $allPlayers->all());
    }

    /**
     * @throws UnknownProperties
     */
    public function getRootContainerId(): ExternalBroadcasterResourceId {
        return new ExternalBroadcasterResourceId([
            "type"           => ExternalResourceType::Container,
            "broadcaster_id" => $this->getBroadcasterId(),
            "external_id"    => $this->config->containerId,
        ]);
    }

    /**
     * @inheritDoc
     * @throws UnknownProperties
     */
    public function getContainer(ExternalBroadcasterResourceId $container): Container|null {
        return BroadSignContainer::get($this->getAPIClient(), $container->external_id)?->toResource();
    }

    /**
     * @param ExternalBroadcasterResourceId $displayType
     * @return DisplayType|null
     * @throws UnknownProperties
     */
    public function getDisplayType(ExternalBroadcasterResourceId $displayType): DisplayType|null {
        return BroadSignDisplayType::get($this->getAPIClient(), (int)$displayType->external_id)?->toResource();
    }

    /**
     * @inheritdoc
     * @throws UnknownProperties
     */
    public function getLocation(ExternalBroadcasterResourceId $location): Location {
        if ($location->type !== ExternalResourceType::Location) {
            throw new InvalidBroadcastResource($location->type, [ExternalResourceType::Location]);
        }

        /** @var BroadSignLocation|null $bsLocation */
        $bsLocation = BroadSignLocation::get($this->getAPIClient(), $location->external_id);

        if (!$bsLocation) {
            throw new MissingExternalResourceException($this->getBroadcasterType(), ExternalResourceType::Location);
        }

        return $bsLocation->toResource();
    }

    /**
     * @param ExternalBroadcasterResourceId $location
     * @return Frame[]
     * @throws InvalidBroadcastResource
     */
    public function getLocationFrames(ExternalBroadcasterResourceId $location): array {
        if ($location->type !== ExternalResourceType::Location) {
            throw new InvalidBroadcastResource($location->type, [ExternalResourceType::Location]);
        }

        $frames = Skin::byDisplayUnit($this->getAPIClient(), ["display_unit_id" => $location->external_id]);
        return $frames->map(fn(Skin $frame) => $frame->toResource())->all();
    }

    /**
     * @inheritdoc
     */
    public function findCampaigns(string $query): array {
        $resources = ResourceQuery::byName($this->getAPIClient(), $query, "reservation");

        if (count($resources) === 0) {
            return [];
        }

        return BroadSignCampaign::byId($this->getAPIClient(), ["ids" => $resources->pluck("id")->values()->join(",")])
                                ->map(fn(BroadSignCampaign $campaign) => new CampaignSearchResult([
                                    ...$campaign->toResource()->toArray(),
                                    "id" => [
                                        "broadcaster_id" => $this->getBroadcasterId(),
                                        "external_id"    => $campaign->getKey(),
                                        "type"           => ExternalResourceType::Campaign,
                                    ],
                                ]))
                                ->all();
    }

    /**
     * @inheritdoc
     */
    public function getCampaignLocations(ExternalBroadcasterResourceId $externalCampaign): array {
        if ($externalCampaign->type !== ExternalResourceType::Campaign) {
            throw new InvalidExternalBroadcasterResourceType(ExternalResourceType::Campaign, $externalCampaign->type);
        }

        $campaign  = new BroadSignCampaign($this->getAPIClient(), ["id" => $externalCampaign->external_id]);
        $locations = $campaign->locations();

        return $locations->map(fn(BroadSignLocation $location) => $location->toResource())->all();
    }

    /**
     * @throws UnknownProperties
     * @throws UnsupportedBroadcasterFunctionalityException
     */
    public function getLocationActiveHours(ExternalBroadcasterResourceId $location): ActiveHours {
        // Opening hours resides in the daypart attached to the location.
        /** @var DayPart|null $dayPart */
        $dayPart = DayPart::getByDisplayUnit($this->getAPIClient(), $location->external_id)
                          ->firstWhere("minute_mask", "!==", "");

        // If no daypart could be found, we assume 24h operations
        if (!$dayPart) {
            return new ActiveHours(days: [
                ["00:00", "23:59"],
                ["00:00", "23:59"],
                ["00:00", "23:59"],
                ["00:00", "23:59"],
                ["00:00", "23:59"],
                ["00:00", "23:59"],
                ["00:00", "23:59"],
            ]);
        }

        // Broadsign stores opening hours in minutes from the start of week, so we need to convert it to regular hours
        // eg: 480-1289;1920-2729;3360-4169;4800-5609;6240-7049;7680-8489;9120-9929
        $days = collect(explode(";", $dayPart->minute_mask));

        // If there is more or less than 7 time range specified, throw an error as we do not support this
        if ($days->count() !== 7) {
            throw new UnsupportedBroadcasterFunctionalityException($this->getBroadcasterType(), "more/less than 7 time periods in a DayPart");
        }

        $days = $days->map(fn($day, $i) => array_map(static function ($time) use ($i) {
            $tmp     = ((int)$time) - ($i * 60 * 24);
            $hours   = floor($tmp / 60);
            $minutes = $tmp % 60;

            return str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT);
        }, explode("-", $day)));

        return new ActiveHours(days: $days->all());
    }

    public function setLocationActiveHours(ExternalBroadcasterResourceId $location, ActiveHours $activeHours): bool {
        // Transform the given opening hour into a format that works for BroadSign
        $mask = implode(";", array_map(static function (array $activeHours, int $i) {
            $openTimes    = explode(":", $activeHours[0]);
            $openHour     = (int)$openTimes[0];
            $openMinutes  = (int)$openTimes[1];
            $closeTimes   = explode(":", $activeHours[1]);
            $closeHour    = (int)$closeTimes[0];
            $closeMinutes = (int)$closeTimes[1];

            $startMask = (24 * 60 * $i) + $openHour * 60 + $openMinutes;
            $endMask   = (24 * 60 * $i) + $closeHour * 60 + $closeMinutes;

            return $startMask . "-" . $endMask;
        }, $activeHours->days, array_keys($activeHours->days)));

        $dayParts = DayPart::getByDisplayUnit($this->getAPIClient(), $location->external_id);

        /** @var DayPart $dayPart */
        foreach ($dayParts as $dayPart) {
            $dayPart->minute_mask = $mask;
            $dayPart->save();
        }

        return true;
    }

    /**
     * @throws UnknownProperties
     */
    public function createCampaign(Campaign $campaign): ExternalBroadcasterResourceId {
        // Make sure the name of the campaign reflects the campaign priority
        $campaignName = $campaign->name;

        if ($campaign->priority > 1) {
            $campaignName .= "_BUA";
        }

        $bsCampaign = new BroadSignCampaign($this->getAPIClient(), [
            "name" => $campaignName,

            "duration_msec" => $campaign->duration_msec,

            "start_date"       => $campaign->start_date,
            "start_time"       => $campaign->start_time,
            "end_date"         => $campaign->end_date,
            "end_time"         => $campaign->end_time,
            "day_of_week_mask" => $campaign->broadcast_days,

            "priority"   => $campaign->priority,
            "saturation" => $campaign->occurrences_in_loop,

            "auto_synchronize_bundles" => true,
            "container_id"             => !$campaign->advertiser ? $this->config->reservationsContainerId : null,
            "parent_id"                => $campaign->advertiser?->external_id ?? $this->config->customerId,
        ]);

        $bsCampaign->create();

        // Promote the campaign once it has been created. This cannot be done on reservation creation
        $bsCampaign->state = BroadSignReservationState::Contracted->value;
        $bsCampaign->save();

        return new ExternalBroadcasterResourceId(
            external_id: $bsCampaign->getKey(),
            broadcaster_id: $this->getBroadcasterId(),
            type: ExternalResourceType::Campaign,
        );
    }

    /**
     * @throws UnknownProperties
     */
    public function checkCampaign(ExternalBroadcasterResourceId $externalCampaign, Campaign $expected): ResourcesComparator {
        $bsCampaign = BroadSignCampaign::get($this->getAPIClient(), $externalCampaign->external_id);

        return new ResourcesComparator($expected, $bsCampaign?->toResource());
    }

    /**
     * @throws UnknownProperties
     */
    public function checkCampaignTargeting(ExternalBroadcasterResourceId $externalCampaign, CampaignTargeting $expected): ResourcesComparator {
        $bsCampaign = new BroadSignCampaign($this->getAPIClient(), ["id" => $externalCampaign->external_id]);

        $campaignTags = $bsCampaign->criteria()->map(fn(ResourceCriteria $resourceCriteria) => new Tag(
            external_id: $resourceCriteria->getKey(),
            name: $resourceCriteria->criteria()->name,
        ));

        $locations = $bsCampaign->locations()
                                ->map(fn(BroadSignLocation $location) => new ExternalBroadcasterResourceId(
                                    type: ExternalResourceType::Location,
                                    broadcaster_id: $this->getBroadcasterId(),
                                    external_id: $location->getKey()
                                ));

        return new ResourcesComparator($expected, new CampaignTargeting(
            campaignTags: $campaignTags,
            locations: $locations,
        ));
    }

    /**
     * @throws UnknownProperties
     * @throws ExternalBroadcastResourceNotFoundException
     * @throws CannotUpdateExternalResourceException
     */
    public function updateCampaign(ExternalBroadcasterResourceId $externalCampaign, Campaign $campaign): ExternalBroadcasterResourceId {
        $bsCampaign = BroadSignCampaign::get($this->getAPIClient(), $externalCampaign->external_id);

        if (is_null($bsCampaign)) {
            throw new ExternalBroadcastResourceNotFoundException($externalCampaign);
        }

        $comparator = new ResourcesComparator($campaign, $bsCampaign->toResource());

        // It is not possible to change some of the properties of a campaign after it has been created.
        $readonlyProperties = ["start_date", "start_time", "end_date", "end_time", "occurrences_in_loop", "broadcast_days", "duration_msec"]; // We don't check priority because BroadSign does not return it when querying a campaign
        $updatable          = true;
        $breakingProperty   = "";

        foreach ($readonlyProperties as $property) {
            if ($comparator->isDifferent($property)) {
                $updatable        = false;
                $breakingProperty = $property;
                break;
            }
        }

        if (!$updatable) {
            clock($breakingProperty);
            throw new CannotUpdateExternalResourceException($this->getType(), "Cannot update a campaign $breakingProperty property after creation");
        }

        // Update non-readonly properties
        if ($comparator->isDifferent(["enabled", "name", "occurrences_in_loop"])) {
            $bsCampaign->active     = $campaign->enabled;
            $bsCampaign->name       = $campaign->name;
            $bsCampaign->saturation = $campaign->occurrences_in_loop;
            $bsCampaign->save();
        }

        return new ExternalBroadcasterResourceId(
            external_id: $bsCampaign->getKey(),
            broadcaster_id: $this->getBroadcasterId(),
            type: ExternalResourceType::Campaign,
        );
    }

    /**
     * @param ExternalBroadcasterResourceId $externalCampaign
     * @param CampaignTargeting             $campaignTargeting
     * @return bool
     */
    public function targetCampaign(ExternalBroadcasterResourceId $externalCampaign, CampaignTargeting $campaignTargeting): bool {
        // Start by making sure the desired tags are applied to the campaign
        $campaignCriteria = ResourceCriteria::for($this->getAPIClient(), $externalCampaign->external_id);

        $bsCampaign = new BroadSignCampaign($this->getAPIClient(), [
            "id" => $externalCampaign->external_id,
        ]);

        $campaignTags = array_map(static fn(Tag $tag) => $tag->external_id, $campaignTargeting->campaignTags);

        /** @var ResourceCriteria $criterion */
        foreach ($campaignCriteria as $criterion) {
            // Is this criterion in our requirements ?
            if (in_array($criterion->id, $campaignTags, true)) {
                // Yes, remove it from our requirements
                $campaignTags = array_filter($campaignTags, static fn(int $tagId) => $tagId !== $criterion->id);
                continue;
            }

            // No, remove it from the server
            $criterion->active = false;
            $criterion->save();
        }

        // We are now left only with the criteria that needs to be added to the campaign.
        foreach ($campaignTags as $tagId) {
            $bsCampaign->addCriteria($tagId, 8);
        }

        $bsCampaignLocations = $bsCampaign->locations();

        $bsCampaign->removeLocations($bsCampaignLocations->map(fn(BroadSignLocation $location) => $location->getKey()));
        $bsCampaign->addLocations($campaignTargeting->getLocationsExternalIds(), $campaignTargeting->getLocationsTagsExternalIds());

        return true;
    }

    public function deleteCampaign(ExternalBroadcasterResourceId $externalCampaign): bool {
        $bsCampaign = BroadSignCampaign::get($this->getAPIClient(), $externalCampaign->external_id);

        if (!$bsCampaign) {
            return false;
        }

        $bsCampaign->active = false;
        $bsCampaign->state  = $bsCampaign->state === BroadSignReservationState::HeldCancelled->value ? BroadSignReservationState::HeldCancelled->value : BroadSignReservationState::Cancelled->value;
        $bsCampaign->save();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCampaignsPerformances(array $campaignIds): array {
        $resourceIds = BroadcasterUtils::extractExternalIds($campaignIds, ExternalResourceType::Campaign);

        $performances = ReservablePerformance::byReservable($this->getAPIClient(), $resourceIds);

        return $performances->map(fn(ReservablePerformance $performance) => $performance->toResource())->all();
    }

    /**
     * @inheritDoc
     * @throws UnknownProperties
     */
    public function createSchedule(Schedule $schedule, ExternalBroadcasterResourceId $campaign, array $tags): array {
        // First get the loop slot for the campaign
        $loopSlots = LoopSlot::forCampaign($this->getAPIClient(), $campaign->external_id);

        if (count($loopSlots) === 0) {
            throw new BroadcastServiceException($this->getType(), "Could not found a loop slot for campaign #$campaign->external_id");
        }

        // Create the schedule
        $bsSchedule       = new BroadSignSchedule($this->getAPIClient());
        $bsSchedule->name = $schedule->name;

        $bsSchedule->start_date       = $schedule->start_date;
        $bsSchedule->start_time       = $schedule->start_time;
        $bsSchedule->end_date         = $schedule->end_date;
        $bsSchedule->end_time         = $schedule->end_time;
        $bsSchedule->day_of_week_mask = $schedule->broadcast_days;

        $bsSchedule->weight         = $schedule->order;
        $bsSchedule->rotation_mode  = ScheduleRotationMode::Ordered->value;
        $bsSchedule->schedule_group = ScheduleGroup::LoopSlot->value;

        $bsSchedule->reservable_id = (int)$campaign->external_id;
        $bsSchedule->parent_id     = $loopSlots[0]->id;

        $bsSchedule->create();

        // If the schedule is not enabled, we disabled the representation in BroadSign to prevent it from playing
        if (!$schedule->enabled) {
            $bsSchedule->active = false;
            $bsSchedule->save();
        }

        // Create the bundle
        $bsBundle       = new BroadSignBundle($this->getAPIClient());
        $bsBundle->name = $bsSchedule->name;

        $bsBundle->max_duration_msec = $schedule->duration_msec;
        $bsBundle->fullscreen        = $schedule->is_fullscreen;

        $bsBundle->auto_synchronized     = true;
        $bsBundle->allow_custom_duration = true;

        $bsBundle->parent_id = $bsSchedule->id;

        $triggerTag = array_filter($tags, static fn(Tag $tag) => $tag->tag_type === BroadcastTagType::Trigger);
        if (count($triggerTag) > 0) {
            $bsBundle->auto_synchronized   = false;
            $bsBundle->trigger_category_id = (int)$triggerTag[0]->external_id;
        }

        $separationTags = array_filter($tags, static fn(Tag $tag) => $tag->tag_type === BroadcastTagType::Category);
        if (count($separationTags) > 0) {
            $bsBundle->category_id = (int)array_shift($separationTags)->external_id;
        }

        if (count($separationTags) > 0) {
            $bsBundle->secondary_sep_category_ids = implode(",", array_map(static fn(Tag $tag) => $tag->external_id, $separationTags));
        }

        $bsBundle->create();

        return [
            new ExternalBroadcasterResourceId(
                type: ExternalResourceType::Schedule,
                broadcaster_id: $this->getBroadcasterId(),
                external_id: $bsSchedule->getKey()
            ),
            new ExternalBroadcasterResourceId(
                type: ExternalResourceType::Bundle,
                broadcaster_id: $this->getBroadcasterId(),
                external_id: $bsBundle->getKey()
            ),
        ];
    }


    /**
     * @inheritDoc
     * @throws ExternalBroadcastResourceNotFoundException
     * @throws UnknownProperties
     */
    public function updateSchedule(array $externalResources, Schedule $schedule, array $tags): array {
        $externalSchedule = $this->getResourceByType($externalResources, ExternalResourceType::Schedule);

        $bsSchedule = BroadSignSchedule::get($this->getAPIClient(), $externalSchedule->external_id);

        if (is_null($bsSchedule)) {
            throw new ExternalBroadcastResourceNotFoundException($externalSchedule);
        }

        // Only update schedule if necessary
        $scheduleComparator = new ResourcesComparator($schedule, $bsSchedule->toResource());
        if ($scheduleComparator->isDifferent(["enabled", "start_date", "start_time", "end_date", "end_time", "broadcast_days", "order"])) {
            $bsSchedule->active           = $schedule->enabled;
            $bsSchedule->start_date       = $schedule->start_date;
            $bsSchedule->start_time       = $schedule->start_time;
            $bsSchedule->end_date         = $schedule->end_date;
            $bsSchedule->end_time         = $schedule->end_time;
            $bsSchedule->day_of_week_mask = $schedule->broadcast_days;
            $bsSchedule->weight           = $schedule->order;
            $bsSchedule->save();
        }

        $externalBundle = $this->getResourceByType($externalResources, ExternalResourceType::Bundle);

        $bsBundle = BroadSignBundle::get($this->getAPIClient(), $externalBundle->external_id);

        if (is_null($bsBundle)) {
            throw new ExternalBroadcastResourceNotFoundException($externalBundle);
        }

        // Only update bundle if necessary
        $triggerTags = array_filter($tags, static fn(Tag $tag) => $tag->tag_type === BroadcastTagType::Trigger);
        $triggerTag  = count($triggerTags) > 0 ? (int)(array_shift($triggerTags)->external_id) : 0;

        $separationTags          = array_filter($tags, static fn(Tag $tag) => $tag->tag_type === BroadcastTagType::Category);
        $primarySeparationTag    = count($separationTags) > 0 ? (int)(array_shift($separationTags)->external_id) : 0;
        $secondarySeparationTags = implode(",", array_map(static fn(Tag $tag) => (int)$tag->external_id, $separationTags));

        if ($schedule->order !== $bsBundle->position ||
            $triggerTag !== $bsBundle->trigger_category_id ||
            $primarySeparationTag !== $bsBundle->category_id ||
            $secondarySeparationTags !== $bsBundle->secondary_sep_category_ids
        ) {
            $bsBundle->auto_synchronized          = $triggerTag !== 0;
            $bsBundle->trigger_category_id        = $triggerTag;
            $bsBundle->category_id                = $primarySeparationTag;
            $bsBundle->secondary_sep_category_ids = $secondarySeparationTags;
            $bsBundle->position                   = $schedule->order;
            $bsBundle->save();
        }

        return [
            $externalSchedule,
            $externalBundle,
        ];
    }

    /**
     * @inheritDoc
     * @throws InvalidBroadcastResource
     */
    public function deleteSchedule(array $externalResources): bool {
        // Loop over all the provided resources. If they are schedules or bundles, deactivate them.
        foreach ($externalResources as $externalResource) {
            switch ($externalResource->type) {
                case ExternalResourceType::Schedule:
                    $bsSchedule = BroadSignSchedule::get($this->getAPIClient(), $externalResource->external_id);

                    // If the resource is not found, just ignore it
                    if (!is_null($bsSchedule)) {
                        $bsSchedule->active = false;
                        $bsSchedule->weight = 0;
                        $bsSchedule->save();
                    }
                    break;
                case ExternalResourceType::Bundle:
                    $bsBundle = BroadSignBundle::get($this->getAPIClient(), $externalResource->external_id);

                    // If the resource is not found, just ignore it
                    if (!is_null($bsBundle)) {
                        $bsBundle->active = false;
                        $bsBundle->save();
                    }
                    break;
                default:
                    throw new InvalidBroadcastResource($externalResource->type, [ExternalResourceType::Schedule, ExternalResourceType::Bundle]);
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function setScheduleContents(array $externalResources, array $creatives) {
        $externalBundle = $this->getResourceByType($externalResources, ExternalResourceType::Bundle);

        // List the creatives already attached to the bundle
        $bundleContents = BundleContent::byBundle($this->getAPIClient(), $externalBundle->external_id);

        /** @var int[] $attachedCreativesIds */
        $attachedCreativesIds = $bundleContents->pluck("content_id")->all();
        /** @var int[] $specifiedCreativesIds */
        $specifiedCreativesIds = array_map(static fn(ExternalBroadcasterResourceId $creative) => $creative->external_id, $creatives);

        // Compute difference between attached ad-copies, and the given list
        $obsoleteAdCopiesIds = array_diff($attachedCreativesIds, $specifiedCreativesIds);
        $missingCreativesIds = array_diff($specifiedCreativesIds, $attachedCreativesIds);

        // Disable all obsolete bundle contents
        foreach ($obsoleteAdCopiesIds as $obsoleteAdCopy) {
            $bundleContents
                ->where("content_id", "=", $obsoleteAdCopy)
                ->each(function (BundleContent $bundleContent) {
                    $bundleContent->active = false;
                    $bundleContent->save();
                });
        }

        // Add missing creatives to the bundle
        foreach ($missingCreativesIds as $creativeId) {
            $bundleContent             = new BundleContent($this->getAPIClient());
            $bundleContent->parent_id  = (int)$externalBundle->external_id;
            $bundleContent->content_id = $creativeId;
            $bundleContent->create();
        }

    }

    /**
     * @inheritDoc
     * @throws UnknownProperties
     */
    public function importCreative(Creative $creative, CreativeStorageType $storageType, array $tags): ExternalBroadcasterResourceId {
        // First, import the creative
        $creativeId = BroadSignCreative::import($this->getAPIClient(), $creative, $storageType);

        $bsCreative = new BroadSignCreative($this->getAPIClient(), ["id" => $creativeId]);

        // Then, target it, only consider targeting tags
        foreach ($tags as $tag) {
            if ($tag->tag_type !== BroadcastTagType::Targeting) {
                continue;
            }
            $bsCreative->addCriteria($tag->external_id, 0);
        }

        return new ExternalBroadcasterResourceId(
            type: ExternalResourceType::Creative,
            broadcaster_id: $this->getBroadcasterId(),
            external_id: $creativeId,
        );
    }

    /**
     * @inheritdoc
     */
    public function updateCreative(ExternalBroadcasterResourceId $externalCreative, array $tags): bool {
        // First, list criteria already attached to the creative
        $existingCriteria    = ResourceCriteria::for($this->getAPIClient(), $externalCreative->external_id)
                                               ->where("active", "=", true);
        $existingCriteriaIds = $existingCriteria->pluck("criteria_id")->all();
        $specifiedTagsIds    = array_map(static fn(Tag $tag) => $tag->external_id, $tags);

        // Compute difference between existing criteria on resource and specified ones
        $obsoleteCriteriaIds = array_diff($existingCriteriaIds, $specifiedTagsIds);
        $missingCriteriaIds  = array_diff($specifiedTagsIds, $existingCriteriaIds);

        // Remove obsolete criteria from the resource
        /** @var ResourceCriteria $criterion */
        foreach ($existingCriteria as $criterion) {
            if (in_array($criterion->criteria_id, $obsoleteCriteriaIds, true)) {
                $criterion->active = false;
                $criterion->save();
            }
        }

        $creative = new BroadSignCreative($this->getAPIClient(), [
            "id" => $externalCreative->external_id,
        ]);

        // Add missing criteria
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            if ($tag->tag_type === BroadcastTagType::Targeting && in_array($tag->external_id, $missingCriteriaIds, true)) {
                $creative->addCriteria($tag->external_id, 0);
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteCreative(ExternalBroadcasterResourceId $externalCreative): bool {
        $bsCreative = BroadSignCreative::get($this->getAPIClient(), $externalCreative->external_id);

        if (!$bsCreative) {
            // Creative is missing, but since we want to deactivate it, it is okay.
            // We still return false as a way to say we didn't do anything
            return false;
        }

        $bsCreative->active = false;
        $bsCreative->save();

        return true;
    }

    /**
     * @inheritDoc
     */
    public function requestScreenshotsBurst(array $players, string $responseUri, int $scale, int $duration_ms, int $frequency_ms): bool {
        $requestID = uniqid("connect-", true);

        foreach ($players as $playerId) {
            $bsPlayer = new BroadSignPlayer($this->getAPIClient(), ["id" => $playerId->external_id]);

            $bsPlayer->requestScreenshotsBurst(
                burstID: $requestID,
                responseUri: $responseUri,
                scale: $scale,
                duration_ms: $duration_ms,
                frequency_ms: $frequency_ms,
            );
        }

        return true;
    }
}
