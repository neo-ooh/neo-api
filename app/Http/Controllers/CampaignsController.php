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

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\BroadSign\Jobs\Campaigns\CreateBroadSignCampaign;
use Neo\BroadSign\Jobs\Campaigns\UpdateBroadSignCampaign;
use Neo\BroadSign\Jobs\Campaigns\UpdateCampaignTargeting;
use Neo\Http\Requests\Campaigns\DestroyCampaignRequest;
use Neo\Http\Requests\Campaigns\ListCampaignsRequest;
use Neo\Http\Requests\Campaigns\StoreCampaignRequest;
use Neo\Http\Requests\Campaigns\UpdateCampaignRequest;
use Neo\Http\Requests\CampaignsLocations\RemoveCampaignLocationRequest;
use Neo\Http\Requests\CampaignsLocations\SyncCampaignLocationsRequest;
use Neo\Models\Campaign;
use Neo\Models\Format;
use Neo\Models\Location;

class CampaignsController extends Controller {
    /**
     * @param ListCampaignsRequest $request
     *
     * @return ResponseFactory|Response
     * @noinspection PhpUnusedParameterInspection
     */
    public function index(ListCampaignsRequest $request) {
        return new Response(Auth::user()->getCampaigns()->load("format:id,name",
            "owner"));
    }

    /**
     * @param StoreCampaignRequest $request
     *
     * @return ResponseFactory|Response
     */
    public function store(StoreCampaignRequest $request) {
        $campaign = new Campaign();
        [
            "owner_id"         => $campaign->owner_id,
            "format_id"        => $campaign->format_id,
            "name"             => $campaign->name,
            "display_duration" => $campaign->display_duration,
            "content_limit"    => $campaign->content_limit,
            "start_date"       => $campaign->start_date,
            "end_date"         => $campaign->end_date,
            "loop_saturation"  => $campaign->loop_saturation,
        ] = $request->validated();

        // If no name was specified for the campaign, we generate one
        if ($campaign->name === null) {
            $campaign->name = Format::query()->find($campaign->format_id)->name;
        }

        $campaign->save();

        $locations = $campaign->owner->locations->whereIn("display_type_id", $campaign->format->display_types->pluck('id'));

        // Copy over the locations of the campaign owner to the campaign itself
        if (count($locations) > 0) {
            $campaign->locations()->attach($locations);
            $campaign->refresh();
        }

        // Replicate the campaign in BroadSign
        CreateBroadSignCampaign::dispatch($campaign->id);

        return new Response($campaign->loadMissing(["format", "owner", "schedules"]), 201);
    }

    /**
     * @param Campaign $campaign
     *
     * @return ResponseFactory|Response
     */
    public function show(Campaign $campaign) {
        return new Response($campaign->loadMissing([
            "format",
            "format.layouts",
            "format.display_types",
            "locations",
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
     * @return ResponseFactory|Response
     */
    public function update(UpdateCampaignRequest $request, Campaign $campaign) {
        [
            "owner_id"         => $campaign->owner_id,
            "name"             => $campaign->name,
            "display_duration" => $campaign->display_duration,
            "content_limit"    => $campaign->content_limit,
            "start_date"       => $campaign->start_date,
            "end_date"         => $campaign->end_date,
            "loop_saturation"  => $campaign->loop_saturation,
        ] = $request->validated();
        $campaign->save();
        $campaign->refresh();

        // Propagate the changes in BroadSign
        UpdateBroadSignCampaign::dispatch($campaign->id);

        return $this->show($campaign);
    }

    /**
     * @param SyncCampaignLocationsRequest $request
     * @param Campaign                     $campaign
     *
     * @return ResponseFactory|Response
     */
    public function syncLocations(SyncCampaignLocationsRequest $request, Campaign $campaign) {
        $locations = $request->validated()['locations'];

        // All good, add the capabilities
        $campaign->locations()->sync($locations);
        $campaign->refresh();

        // Propagate the changes in BroadSign
        UpdateCampaignTargeting::dispatch($campaign->id);

        return new Response($campaign->locations);
    }

    public function removeLocation(RemoveCampaignLocationRequest $request, Campaign $campaign, Location $location): Response {
        $campaign->locations()->detach($location);
        $campaign->refresh();

        // Propagate the changes in BroadSign
        UpdateCampaignTargeting::dispatch($campaign->id);

        return new Response($campaign->locations);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function destroy(DestroyCampaignRequest $request, Campaign $campaign): void {
        $campaign->delete();
    }
}
