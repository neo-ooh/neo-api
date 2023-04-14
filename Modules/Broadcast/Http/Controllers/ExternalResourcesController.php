<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExternalResourcesController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Exceptions\InvalidRequestException;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcastResource;
use Neo\Modules\Broadcast\Http\Requests\ExternalResources\DestroyExternalResourceRequest;
use Neo\Modules\Broadcast\Jobs\Campaigns\DeleteCampaignJob;
use Neo\Modules\Broadcast\Jobs\Creatives\DeleteCreativeJob;
use Neo\Modules\Broadcast\Jobs\Schedules\DeleteScheduleJob;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Services\ExternalCampaignDefinition;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

class ExternalResourcesController extends Controller {
    /**
     * @param DestroyExternalResourceRequest $request
     * @param ExternalResource               $externalResource
     * @return Response
     * @throws InvalidBroadcasterAdapterException
     * @throws InvalidBroadcastResource
     */
    public function destroy(DestroyExternalResourceRequest $request, ExternalResource $externalResource): Response {
        switch ($externalResource->type) {
            case ExternalResourceType::Creative:
                $job = new DeleteCreativeJob($externalResource->resource_id, $externalResource->getKey());
                $job->handle();
                break;
            case ExternalResourceType::Schedule:
            case ExternalResourceType::Bundle:
                $schedule  = Schedule::withTrashed()->find($externalResource->resource_id);
                $formatIds = $externalResource->data->formats_id ?? [];

                foreach ($formatIds as $formatId) {
                    $campaignDefinition = new ExternalCampaignDefinition(
                        campaign_id: $schedule->campaign_id,
                        network_id : $externalResource->data->network_id,
                        format_id  : $formatId,
                        locations  : ExternalBroadcasterResourceId::collection([]),
                    );

                    $job = new DeleteScheduleJob($schedule->getKey(), $campaignDefinition);
                    $job->handle();
                }

                break;
            case ExternalResourceType::Campaign:
                $job = new DeleteCampaignJob($externalResource->resource_id, $externalResource->getKey());
                $job->handle();
                break;
            case ExternalResourceType::Location:
            case ExternalResourceType::Player:
            case ExternalResourceType::Container:
            case ExternalResourceType::DisplayType:
            case ExternalResourceType::Tag:
            case ExternalResourceType::Frame:
            case ExternalResourceType::Advertiser:
                throw new InvalidRequestException("This external resource cannot be deleted");
        }

        return new Response(["status" => "ok"]);
    }
}
