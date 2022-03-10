<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastService.php
 */

namespace Neo\Services\Broadcast;

use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;

interface BroadcastService {
    public function getConfig(): BroadSignConfig|PiSignageConfig;
    /**
     * Synchronize stored locations with the location of the network
     *
     * @return mixed
     */
    public function synchronizeLocations();
    /**
     * Synchronize stored players with the players of the network
     *
     * @return mixed
     */
    public function synchronizePlayers();

    /*
    |--------------------------------------------------------------------------
    | Creatives
    |--------------------------------------------------------------------------
    */

    /**
     * Deletes the specified creative from the broadcaster's servers
     *
     * @param string $creative_external_id
     * @return mixed
     */
    public function destroyCreative(string $creative_external_id);

    /*
    |--------------------------------------------------------------------------
    | Schedules
    |--------------------------------------------------------------------------
    */

    /**
     * replicate the schedule in the broadcast Service
     *
     * @param int $scheduleId
     * @param int $actorId
     * @return mixed
     */
    public function createSchedule(int $scheduleId, int $actorId);

    /**
     * Update the schedule
     *
     * @param int $scheduleId
     * @return mixed
     */
    public function updateSchedule(int $scheduleId);

    /**
     * Enable the schedule for broadcasting. Schedule broadcasting is still dependant on the conditions set on the schedule.
     *
     * @param int $scheduleId
     * @return mixed
     */
    public function enableSchedule(int $scheduleId);

    /**
     * Remove the schedule from broadcasting
     *
     * @param int $scheduleId
     * @return mixed
     */
    public function disableSchedule(int $scheduleId);

    /**
     * Destroy the schedule, it will need to be re-created to broadcast it again
     *
     * @param int $scheduleId
     * @return mixed
     */
    public function destroySchedule(int $scheduleId);

    /*
    |--------------------------------------------------------------------------
    | Campaigns
    |--------------------------------------------------------------------------
    */

    public function searchCampaigns(array $query);

    /**
     * Replicate the campaign in the broadcast service
     *
     * @param int $campaignId
     * @return mixed
     */
    public function createCampaign(int $campaignId);

    /**
     * Update the campaign information
     *
     * @param int $campaignId
     * @return mixed
     */
    public function updateCampaign(int $campaignId);

    /**
     * Properly target, or retarget the campaign
     *
     * @param int $campaignId
     * @return mixed
     */
    public function targetCampaign(int $campaignId);

    /**
     * Update the order of the schedules in the campaign to match the one specified by Connect
     *
     * @param int $campaignId
     * @return mixed
     */
    public function updateCampaignSchedulesOrder(int $campaignId);

    /**
     * Delete, or disable, the campaign. A disabled campaign has to be created again to be broadcasted again.
     *
     * @param int $campaignId
     * @return mixed
     */
    public function destroyCampaign(int $campaignId);

    /**
     * Rebuild the campaign. The campaign will be deleted then re-created and targeted
     *
     * @param int $campaignId
     * @return mixed
     */
    public function rebuildCampaign(int $campaignId);
}
