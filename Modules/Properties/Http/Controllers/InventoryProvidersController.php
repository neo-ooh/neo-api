<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryProvidersController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\ClearInventoryCacheRequest;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\ListInventoriesRequest;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\RemoveInventoryRequest;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\ShowInventoryRequest;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\StoreInventoryRequest;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\SyncInventoryRequest;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\UpdateInventoryRequest;
use Neo\Modules\Properties\Http\Requests\InventoryProviders\ValidateInventoryConfigurationCacheRequest;
use Neo\Modules\Properties\Jobs\PullFullInventoryJob;
use Neo\Modules\Properties\Jobs\PushFullInventoryJob;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Models\StructuredColumns\InventoryProviderSettings;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\InventoryType;

class InventoryProvidersController extends Controller {
	public function index(ListInventoriesRequest $request) {
		$inventories = InventoryProvider::all();

		if ($request->has("capabilities")) {
			$capabilities       = $request->input("capabilities", []);
			$capabilities_count = count($capabilities);

			$inventories = $inventories->filter(function (InventoryProvider $provider) use ($capabilities, $capabilities_count) {
				$inventory = InventoryAdapterFactory::make($provider);
				return collect($inventory->getCapabilities())
						->map(fn(InventoryCapability $c) => $c->value) // array_intersect only accept stringable values. BackedEnums are not stringable by default
						->intersect($capabilities)->count() === $capabilities_count;
			});
		}

		return new Response($inventories->loadPublicRelations());
	}

	public function store(StoreInventoryRequest $request) {
		$provider           = new InventoryProvider();
		$provider->name     = $request->input("name");
		$provider->provider = $request->input("provider");

		$provider->is_active  = true;
		$provider->auto_pull  = $provider->provider === InventoryType::Dummy ? false : $request->input('auto_pull');
		$provider->allow_pull = $provider->provider === InventoryType::Dummy ? false : $request->input('auto_pull');
		$provider->auto_push  = $provider->provider === InventoryType::Dummy ? false : $request->input('auto_push');
		$provider->allow_push = $provider->provider === InventoryType::Dummy ? false : $request->input('auto_push');

		$provider->settings = new InventoryProviderSettings();

		switch ($provider->provider) {
			case InventoryType::Odoo:
				$provider->settings->api_url      = $request->input("api_url");
				$provider->settings->api_key      = $request->input("api_key");
				$provider->settings->api_username = $request->input("api_username");
				$provider->settings->database     = $request->input("database");
				break;
			case InventoryType::Hivestack:
				$provider->settings->api_url = $request->input("api_url");
				$provider->settings->api_key = $request->input("api_key");
				break;
			case InventoryType::PlaceExchange:
				$provider->settings->api_url      = $request->input("api_url");
				$provider->settings->api_key      = $request->input("api_key");
				$provider->settings->api_username = $request->input("api_username");
				$provider->settings->client_id    = $request->input("client_id");
				$provider->settings->usd_cad_rate = $request->input("usd_cad_rate");
				break;
			case InventoryType::Reach:
				$provider->settings->auth_url     = $request->input("auth_url");
				$provider->settings->api_url      = $request->input("api_url");
				$provider->settings->api_key      = $request->input("api_key");
				$provider->settings->api_username = $request->input("api_username");
				$provider->settings->publisher_id = $request->input("publisher_id");
				$provider->settings->client_id    = $request->input("client_id");
				break;
			case InventoryType::Vistar:
				$provider->settings->api_url      = $request->input("api_url");
				$provider->settings->api_key      = $request->input("api_key");
				$provider->settings->api_username = $request->input("api_username");
				break;
			case InventoryType::Dummy:
		}

		$provider->save();

		return new Response($provider, 201);
	}

	public function show(ShowInventoryRequest $request, InventoryProvider $inventoryProvider) {
		return new Response($inventoryProvider->loadPublicRelations());
	}

	public function update(UpdateInventoryRequest $request, InventoryProvider $inventoryProvider) {
		$inventoryProvider->name = $request->input("name");

		$inventoryProvider->is_active  = $request->input("is_active");
		$inventoryProvider->allow_pull = $request->input('allow_pull');
		$inventoryProvider->auto_pull  = $request->input('auto_pull');
		$inventoryProvider->allow_push = $request->input('allow_push');
		$inventoryProvider->auto_push  = $request->input('auto_push');

		switch ($inventoryProvider->provider) {
			case InventoryType::Odoo:
				$inventoryProvider->settings->api_url      = $request->input("api_url");
				$inventoryProvider->settings->api_key      = $request->input("api_key", $inventoryProvider->settings->api_key);
				$inventoryProvider->settings->api_username = $request->input("api_username");
				$inventoryProvider->settings->database     = $request->input("database");
				break;
			case InventoryType::Hivestack:
				$inventoryProvider->settings->api_url  = $request->input("api_url");
				$inventoryProvider->settings->api_key  = $request->input("api_key", $inventoryProvider->settings->api_key);
				$inventoryProvider->settings->networks = $request->input("networks");
				break;
			case InventoryType::PlaceExchange:
				$inventoryProvider->settings->api_url      = $request->input("api_url");
				$inventoryProvider->settings->api_key      = $request->input("api_key", $inventoryProvider->settings->api_key);
				$inventoryProvider->settings->api_username = $request->input("api_username");
				$inventoryProvider->settings->client_id    = $request->input("client_id");
				$inventoryProvider->settings->networks     = $request->input("networks");
				$inventoryProvider->settings->usd_cad_rate = $request->input("usd_cad_rate");
			case InventoryType::Reach:
				$inventoryProvider->settings->auth_url     = $request->input("auth_url");
				$inventoryProvider->settings->api_url      = $request->input("api_url");
				$inventoryProvider->settings->api_key      = $request->input("api_key", $inventoryProvider->settings->api_key);
				$inventoryProvider->settings->api_username = $request->input("api_username");
				$inventoryProvider->settings->publisher_id = $request->input("publisher_id");
				$inventoryProvider->settings->client_id    = $request->input("client_id");
			case InventoryType::Vistar:
				$inventoryProvider->settings->api_url      = $request->input("api_url");
				$inventoryProvider->settings->api_key      = $request->input("api_key", $inventoryProvider->settings->api_key);
				$inventoryProvider->settings->api_username = $request->input("api_username");
				$inventoryProvider->settings->networks     = $request->input("networks");
		}

		$inventoryProvider->save();

		return new Response($inventoryProvider->loadPublicRelations(), 201);
	}

	public function clearCache(ClearInventoryCacheRequest $request, InventoryProvider $inventoryProvider) {
		$inventoryProvider->clearCache();

		return new Response([], 202);
	}

	/**
	 * @param ValidateInventoryConfigurationCacheRequest $request
	 * @param InventoryProvider                          $inventoryProvider
	 * @return Response
	 * @throws InvalidInventoryAdapterException
	 */
	public function validateConfiguration(ValidateInventoryConfigurationCacheRequest $request, InventoryProvider $inventoryProvider) {
		return new Response([
			                    "status" => $inventoryProvider->getAdapter()->validateConfiguration(),
		                    ]);
	}

	public function sync(SyncInventoryRequest $request, InventoryProvider $inventoryProvider) {
		switch ($request->input("action")) {
			case "push":
				PushFullInventoryJob::dispatch($inventoryProvider->getKey());
				break;
			case "pull":
				PullFullInventoryJob::dispatch($inventoryProvider->getKey());
				break;
		}

		return new Response([], 202);
	}

	public function destroy(RemoveInventoryRequest $request, InventoryProvider $inventoryProvider) {
		$inventoryProvider->delete();

		return new Response([], 202);
	}
}
