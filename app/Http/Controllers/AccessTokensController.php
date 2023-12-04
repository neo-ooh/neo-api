<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessTokensController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Neo\Http\Requests\AccessTokens\DestroyAccessTokenRequest;
use Neo\Http\Requests\AccessTokens\ListAccessTokensRequest;
use Neo\Http\Requests\AccessTokens\ShowAccessTokenRequest;
use Neo\Http\Requests\AccessTokens\StoreAccessTokenRequest;
use Neo\Http\Requests\AccessTokens\UpdateAccessTokenRequest;
use Neo\Models\AccessToken;

class AccessTokensController extends Controller {
	/**
	 * @param ListAccessTokensRequest $request
	 * @return Response
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function index(ListAccessTokensRequest $request): Response {
		return new Response(AccessToken::query()
		                               ->when($request->has("capability"), function (Builder $query) use ($request) {
			                               $query->whereHas("capabilities", function (Builder $query) use ($request) {
				                               $query->where("slug", "=", $request->input("capability"));
			                               });
		                               })
		                               ->orderBy("name")
		                               ->get());
	}

	/**
	 * @param ShowAccessTokenRequest $request
	 * @param AccessToken            $accessToken
	 * @return Response
	 */
	public function show(ShowAccessTokenRequest $request, AccessToken $accessToken): Response {
		return new Response($accessToken);
	}

	public function store(StoreAccessTokenRequest $request): Response {
		$inputs            = $request->validated();
		$accessToken       = new AccessToken();
		$accessToken->name = $inputs["name"];
		$accessToken->save();

		$accessToken->capabilities()->attach($inputs["capabilities"]);

		return new Response($accessToken->refresh(), 201);
	}

	public function update(UpdateAccessTokenRequest $request, AccessToken $accessToken): Response {
		$inputs            = $request->validated();
		$accessToken->name = $inputs["name"];
		$accessToken->save();

		$accessToken->capabilities()->sync($inputs["capabilities"]);

		return new Response($accessToken->refresh());
	}

	public function destroy(DestroyAccessTokenRequest $request, AccessToken $accessToken): Response {
		$accessToken->delete();

		return new Response();
	}
}
