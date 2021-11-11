<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Exceptions\InvalidBroadcastServiceException;
use Neo\Http\Requests\Campaigns\DestroyCampaignRequest;
use Neo\Http\Requests\Campaigns\ListCampaignsRequest;
use Neo\Http\Requests\Campaigns\StoreCampaignRequest;
use Neo\Http\Requests\Campaigns\UpdateCampaignRequest;
use Neo\Http\Requests\CampaignsLocations\RemoveCampaignLocationRequest;
use Neo\Http\Requests\CampaignsLocations\SyncCampaignLocationsRequest;
use Neo\Models\Campaign;
use Neo\Models\Format;
use Neo\Models\Location;
use Neo\Services\Broadcast\Broadcast;

class CampaignsController extends Controller {
    /**
     * @param ListCampaignsRequest $request
     *
     * @return Response
     * @noinspection PhpUnusedParameterInspection
     */
    public function index(ListCampaignsRequest $request): Response {
        return new Response(Auth::user()->getCampaigns()->load("format:id,name",
            "owner"));
    }

    /**
     * @param StoreCampaignRequest $request
     *
     * @return Response
     * @throws InvalidBroadcastServiceException
     */
    public function store(StoreCampaignRequest $request): Response {
        $campaign = new Campaign();
        [
            "network_id"           => $campaign->network_id,
            "owner_id"             => $campaign->owner_id,
            "format_id"            => $campaign->format_id,
            "name"                 => $campaign->name,
            "schedules_max_length" => $campaign->schedules_default_length,
            "schedules_max_length" => $campaign->schedules_max_length,
            "start_date"           => $campaign->start_date,
            "end_date"             => $campaign->end_date,
            "loop_saturation"      => $campaign->loop_saturation,
            "priority"             => $campaign->priority,
        ] = $request->validated();

        // If no name was specified for the campaign, we generate one
        if ($campaign->name === null) {
            $campaign->name = Format::query()->find($campaign->format_id)->name;
        }

        $campaign->save();

        // Get the display types associated with the format that matches the campaign's network's connection.
        $displayTypes = $campaign->format->display_types()
                                         ->join("broadcasters_connections", "display_types.connection_id", "=", "broadcasters_connections.id")
                                         ->where("broadcasters_connections.id", "=", $campaign->network->connection_id)->get();


        $locations = $campaign->owner->locations->whereIn("display_type_id", $displayTypes->pluck('id'));

        // Copy over the locations of the campaign owner to the campaign itself
        if (count($locations) > 0) {
            $campaign->locations()->attach($locations);
            $campaign->refresh();
        }

        // Replicate the campaign in the appropriate broadcaster
        Broadcast::network($campaign->network_id)->createCampaign($campaign->id);

        return new Response($campaign->loadMissing(["format", "owner", "schedules"]), 201);
    }

    /**
     * @param Campaign $campaign
     *
     * @return Response
     */
    public function show(Campaign $campaign): Response {
        return new Response($campaign->loadMissing([
            "format",
            "format.layouts",
            "format.display_types",
            "locations",
            "network",
            "owner",
            "shares",
            "schedules",
            "schedules.content",
            "schedules.owner:id,name",
            "trashedSchedules",
            "trashedSchedules.content"])->append("related_libraries"));
    }

    /**
     * @param UpdateCampaignRequest $request
     * @param Campaign              $campaign
     *
     * @return Response
     * @throws InvalidBroadcastServiceException
     */
    public function update(UpdateCampaignRequest $request, Campaign $campaign): Response {
        [
            "owner_id"                 => $campaign->owner_id,
            "name"                     => $campaign->name,
            "schedules_default_length" => $campaign->schedules_default_length,
            "schedules_max_length"     => $campaign->schedules_max_length,
            "start_date"               => $campaign->start_date,
            "end_date"                 => $campaign->end_date,
            "loop_saturation"          => $campaign->loop_saturation,
        ] = $request->validated();

        $campaign->save();
        $campaign->refresh();

        // Propagate the changes in BroadSign
        Broadcast::network($campaign->network_id)->updateCampaign($campaign->id);

        return $this->show($campaign);
    }

    /**
     * @param SyncCampaignLocationsRequest $request
     * @param Campaign                     $campaign
     *
     * @return Response
     * @throws InvalidBroadcastServiceException
     */
    public function syncLocations(SyncCampaignLocationsRequest $request, Campaign $campaign): Response {
        $locations = $request->validated()['locations'];

        // All good, sync the locations
        $campaign->locations()->sync($locations);
        $campaign->refresh();

        // Propagate the changes in BroadSign
        Broadcast::network($campaign->network_id)->targetCampaign($campaign->id);

        return new Response($campaign->locations);
    }

    /**
     * @throws InvalidBroadcastServiceException
     */
    public function removeLocation(RemoveCampaignLocationRequest $request, Campaign $campaign, Location $location): Response {
        $campaign->locations()->detach($location);
        $campaign->refresh();

        // Propagate the changes in BroadSign
        Broadcast::network($campaign->network_id)->targetCampaign($campaign->id);

        return new Response($campaign->locations);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function destroy(DestroyCampaignRequest $request, Campaign $campaign): Response {
        $campaign->delete();

        return new Response(["result" => "ok"]);
    }
}
