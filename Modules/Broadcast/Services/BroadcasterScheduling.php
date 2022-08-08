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
use Neo\Modules\Broadcast\Services\Resources\CampaignTargeting;
use Neo\Modules\Broadcast\Services\Resources\Content;
use Neo\Modules\Broadcast\Services\Resources\Creative;
use Neo\Modules\Broadcast\Services\Resources\CreativeStorageType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Neo\Modules\Broadcast\Services\Resources\Schedule;
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
     * @param string $query
     * @return array<Campaign>
     */
    public function findCampaigns(string $query): array;

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
     * @param Schedule                             $schedule
     * @param ExternalBroadcasterResourceId        $campaign
     * @param Content                              $content
     * @param array<ExternalBroadcasterResourceId> $creatives
     * @return array<ExternalBroadcasterResourceId>
     */
    public function createSchedule(Schedule $schedule, ExternalBroadcasterResourceId $campaign, Content $content, array $creatives): array;

    /**
     * @param array<ExternalBroadcasterResourceId> $externalResources
     * @param Schedule                             $schedule
     * @return array<ExternalBroadcasterResourceId>
     * @throws CannotUpdateExternalResourceException
     */
    public function updateSchedule(array $externalResources, Schedule $schedule): array;

    /**
     * @param array<ExternalBroadcasterResourceId> $externalResources
     * @return bool
     */
    public function deleteSchedule(array $externalResources): bool;


    /*
    |--------------------------------------------------------------------------
    | Creatives handles
    |--------------------------------------------------------------------------
    */

    /**
     * @param Creative            $creative
     * @param CreativeStorageType $storageType How the creative should be stored.
     * @return ExternalBroadcasterResourceId
     */
    public function importCreative(Creative $creative, CreativeStorageType $storageType): ExternalBroadcasterResourceId;

    /**
     * @param ExternalBroadcasterResourceId $externalCreative
     * @return bool
     */
    public function deleteCreative(ExternalBroadcasterResourceId $externalCreative): bool;
}
