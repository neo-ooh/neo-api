<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignsProductsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\CampaignProducts\ListCampaignProductsRequest;
use Neo\Modules\Broadcast\Http\Requests\CampaignProducts\RemoveCampaignProductRequest;
use Neo\Modules\Broadcast\Http\Requests\CampaignProducts\SyncCampaignProductsRequest;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Properties\Models\Product;

class CampaignsProductsController extends Controller {
	public function index(ListCampaignProductsRequest $request, Campaign $campaign): Response {
		return new Response($campaign->products->loadPublicRelations());
	}

	/**
	 * @param SyncCampaignProductsRequest $request
	 * @param Campaign                    $campaign
	 *
	 * @return Response
	 */
	public function sync(SyncCampaignProductsRequest $request, Campaign $campaign): Response {
		$products = collect($request->input("products"));
		$campaign->products()->sync($products);

		$campaign->promote();

		return new Response($campaign->products);
	}

	public function remove(RemoveCampaignProductRequest $request, Campaign $campaign, Product $product): Response {
		$campaign->products()->detach($product->getKey());
		$campaign->refresh();

		$campaign->promote();

		return new Response($campaign->products);
	}
}
