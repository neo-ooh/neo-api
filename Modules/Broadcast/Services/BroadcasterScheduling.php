<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterScheduling.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Exceptions\ExternalBroadcastResourceNotFoundException;
use Neo\Modules\Broadcast\Services\Exceptions\CannotUpdateExternalResourceException;
use Neo\Modules\Broadcast\Services\Resources\Campaign;
use Neo\Modules\Broadcast\Services\Resources\CampaignSearchResult;
use Neo\Modules\Broadcast\Services\Resources\CampaignTargeting;
use Neo\Modules\Broadcast\Services\Resources\Creative;
use Neo\Modules\Broadcast\Services\Resources\CreativeStorageType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Broadcast\Services\Resources\Schedule;
use Neo\Modules\Broadcast\Services\Resources\Tag;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * Handles on a broadcaster for scheduling content
 */
interface BroadcasterScheduling {
    /*
    |--------------------------------------------------------------------------
    | Campaigns handles
    |--------------------------------------------------------------------------
    */

    /**
     * Search campaigns by name on the broadcaster
     *
     * @param string $query
     * @return array<CampaignSearchResult>
     */
    public function findCampaigns(string $query): array;

    /**
     * List locations attached to the given campaign on the broadcaster
     *
     * @param ExternalBroadcasterResourceId $externalCampaign
     * @return ExternalBroadcasterResourceId[]
     */
    public function getCampaignLocations(ExternalBroadcasterResourceId $externalCampaign): array;

    /**
     * @param Campaign $campaign
     * @return ExternalBroadcasterResourceId
     */
    public function createCampaign(Campaign $campaign): ExternalBroadcasterResourceId;

    /**
     * @param ExternalBroadcasterResourceId $externalCampaign
     * @param Campaign                      $expected
     * @return ResourcesComparator
     */
    public function checkCampaign(ExternalBroadcasterResourceId $externalCampaign, Campaign $expected): ResourcesComparator;

    /**
     * @param ExternalBroadcasterResourceId $externalCampaign
     * @param CampaignTargeting             $expected
     * @return ResourcesComparator
     */
    public function checkCampaignTargeting(ExternalBroadcasterResourceId $externalCampaign, CampaignTargeting $expected): ResourcesComparator;

    /**
     * @param ExternalBroadcasterResourceId $externalCampaign
     * @param Campaign                      $campaign
     * @return ExternalBroadcasterResourceId
     * @throws CannotUpdateExternalResourceException
     * @throws ExternalBroadcastResourceNotFoundException
     * @throws UnknownProperties
     */
    public function updateCampaign(ExternalBroadcasterResourceId $externalCampaign, Campaign $campaign): ExternalBroadcasterResourceId;

    /**
     * @param ExternalBroadcasterResourceId $externalCampaign
     * @param CampaignTargeting             $campaignTargeting
     * @return bool
     */
    public function targetCampaign(ExternalBroadcasterResourceId $externalCampaign, CampaignTargeting $campaignTargeting): bool;

    /**
     * @param ExternalBroadcasterResourceId $externalCampaign
     * @return bool
     */
    public function deleteCampaign(ExternalBroadcasterResourceId $externalCampaign): bool;


    /*
    |--------------------------------------------------------------------------
    | Schedules handles
    |--------------------------------------------------------------------------
    */

    /**
     * Create an empty schedule set to broadcast at the specified dates, times and days
     *
     * @param Schedule                      $schedule
     * @param ExternalBroadcasterResourceId $campaign
     * @param array<Tag>                    $tags
     * @return array<ExternalBroadcasterResourceId>
     */
    public function createSchedule(Schedule $schedule, ExternalBroadcasterResourceId $campaign, array $tags): array;

    /**
     * @param array<ExternalBroadcasterResourceId> $externalResources
     * @param Schedule                             $schedule
     * @param array<Tag>                           $tags
     * @return array<ExternalBroadcasterResourceId>
     * @throws CannotUpdateExternalResourceException
     */
    public function updateSchedule(array $externalResources, Schedule $schedule, array $tags): array;

    /**
     * @param array<ExternalBroadcasterResourceId> $externalResources
     * @return bool
     */
    public function deleteSchedule(array $externalResources): bool;

    /*
    |--------------------------------------------------------------------------
    | Contents Handles
    |--------------------------------------------------------------------------
    */

    /**
     * Set which creatives a schedule should broadcast
     *
     * @param ExternalBroadcasterResourceId[] $externalResources
     * @param ExternalBroadcasterResourceId[] $creatives
     * @return mixed
     */
    public function setScheduleContents(array $externalResources, array $creatives);


    /*
    |--------------------------------------------------------------------------
    | Creatives handles
    |--------------------------------------------------------------------------
    */

    /**
     * @param Creative            $creative
     * @param CreativeStorageType $storageType How the creative should be stored.
     * @param array<Tag>          $tags
     * @return ExternalBroadcasterResourceId
     */
    public function importCreative(Creative $creative, CreativeStorageType $storageType, array $tags): ExternalBroadcasterResourceId;

    /**
     * @param ExternalBroadcasterResourceId $externalCreative
     * @param array<Tag>                    $tags
     * @return bool
     */
    public function updateCreative(ExternalBroadcasterResourceId $externalCreative, array $tags): bool;

    /**
     * @param ExternalBroadcasterResourceId $externalCreative
     * @return bool
     */
    public function deleteCreative(ExternalBroadcasterResourceId $externalCreative): bool;
}
