<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesNetworksController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\PropertiesNetworks\DestroyPropertyNetworkRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesNetworks\ListPropertiesNetworksRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesNetworks\ShowPropertyNetworkRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesNetworks\StorePropertyNetworkRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesNetworks\UpdatePropertyNetworkRequest;
use Neo\Modules\Properties\Models\PropertyNetwork;

class PropertiesNetworksController extends Controller {
	public function index(ListPropertiesNetworksRequest $request) {
		return new Response(PropertyNetwork::query()->get()->loadPublicRelations());
	}

	public function store(StorePropertyNetworkRequest $request) {
		$network               = new PropertyNetwork();
		$network->name         = $request->input("name");
		$network->slug         = $request->input("slug");
		$network->color        = $request->input("color");
		$network->ooh_sales    = $request->input("ooh_sales");
		$network->mobile_sales = $request->input("mobile_sales");
		$network->save();

		return new Response($network, 201);
	}

	public function show(ShowPropertyNetworkRequest $request, PropertyNetwork $propertyNetwork) {
		return new Response($propertyNetwork->loadPublicRelations());
	}

	public function update(UpdatePropertyNetworkRequest $request, PropertyNetwork $propertyNetwork) {
		$propertyNetwork->name         = $request->input("name");
		$propertyNetwork->slug         = $request->input("slug");
		$propertyNetwork->color        = $request->input("color");
		$propertyNetwork->ooh_sales    = $request->input("ooh_sales");
		$propertyNetwork->mobile_sales = $request->input("mobile_sales");
		$propertyNetwork->save();

		return new Response($propertyNetwork->loadPublicRelations());
	}

	public function destroy(DestroyPropertyNetworkRequest $request, PropertyNetwork $propertyNetwork) {
		$propertyNetwork->delete();

		return new Response([]);
	}
}
