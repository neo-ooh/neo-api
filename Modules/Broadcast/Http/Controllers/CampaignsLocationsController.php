<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignsLocationsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\CampaignsLocations\ListCampaignLocationsRequest;
use Neo\Modules\Broadcast\Http\Requests\CampaignsLocations\RemoveCampaignLocationRequest;
use Neo\Modules\Broadcast\Http\Requests\CampaignsLocations\SyncCampaignLocationsRequest;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Location;

class CampaignsLocationsController extends Controller {
	public function index(ListCampaignLocationsRequest $request, Campaign $campaign): Response {
		return new Response($campaign->locations->loadPublicRelations());
	}

	/**
	 * @param SyncCampaignLocationsRequest $request
	 * @param Campaign                     $campaign
	 *
	 * @return Response
	 */
	public function sync(SyncCampaignLocationsRequest $request, Campaign $campaign): Response {
		$locations = collect($request->input("locations"));
		$campaign->locations()
		         ->sync($locations->mapWithKeys(fn(array $locationDefinition) => [
			         $locationDefinition["location_id"] =>
				         ["format_id" => $locationDefinition["format_id"]],
		         ]));

		$campaign->promote();

		return new Response($campaign->locations);
	}

	public function remove(RemoveCampaignLocationRequest $request, Campaign $campaign, Location $location): Response {
		$campaign->locations()->detach($location->getKey());
		$campaign->refresh();

		$campaign->promote();

		return new Response($campaign->locations);
	}
}
