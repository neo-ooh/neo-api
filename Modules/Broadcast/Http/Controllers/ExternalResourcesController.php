<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
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
use Neo\Modules\Broadcast\Http\Requests\ExternalResources\DestroyExternalResourceRequest;
use Neo\Modules\Broadcast\Models\ExternalResource;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class ExternalResourcesController extends Controller {
    /**
     * @throws UnknownProperties
     * @throws InvalidBroadcasterAdapterException
     */
    public function destroy(DestroyExternalResourceRequest $request, ExternalResource $externalResource): Response {
        switch ($externalResource->type) {
            case ExternalResourceType::Creative:
                $network = Network::withTrashed()
                                  ->where("connection_id", "=", $externalResource->broadcaster_id)
                                  ->first();

                if (!$network) {
                    throw new InvalidRequestException("Could not found a network for this broadcaster");
                }

                /** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
                $broadcaster = BroadcasterAdapterFactory::makeForNetwork($network->getKey());
                $broadcaster->deleteCreative($externalResource->toResource());
                $externalResource->delete();
                break;
            case ExternalResourceType::Schedule:
            case ExternalResourceType::Bundle:
                /** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
                $broadcaster = BroadcasterAdapterFactory::makeForNetwork($externalResource->data->network_id);

                // Schedules may have multiple resources representing them. Pull connex resources as well
                /** @var Schedule $schedule */
                $schedule          = Schedule::withTrashed()->find($externalResource->resource_id);
                $externalResources = $schedule->getExternalRepresentation($broadcaster->getBroadcasterId(), $broadcaster->getNetworkId(), $externalResource->data->formats_id[0]);

                $broadcaster->deleteSchedule(array_map(fn(ExternalResource $r) => $r->toResource(), $externalResources));

                foreach ($externalResources as $resource) {
                    $resource->delete();
                }

                break;
            case ExternalResourceType::Campaign:
                /** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
                $broadcaster = BroadcasterAdapterFactory::makeForNetwork($externalResource->data->network_id);
                $broadcaster->deleteCampaign($externalResource->toResource());
                $externalResource->delete();
                break;
            case ExternalResourceType::Location:
            case ExternalResourceType::Player:
            case ExternalResourceType::Container:
            case ExternalResourceType::DisplayType:
            case ExternalResourceType::Tag:
                throw new InvalidRequestException("This external resource cannot be deleted");
        }

        return new Response(["status" => "ok"]);
    }
}
