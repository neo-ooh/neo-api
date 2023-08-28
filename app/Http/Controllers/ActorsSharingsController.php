<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorsSharingsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Requests\ActorsShares\DestroyShareRequest;
use Neo\Http\Requests\ActorsShares\StoreShareRequest;
use Neo\Http\Requests\ActorsShares\SyncAdditionalAccesssesRequest;
use Neo\Models\Actor;

class ActorsSharingsController extends Controller {
	public function index(Actor $actor): Response {
		Gate::authorize(Capability::actors_edit->value);

		// Validate authenticated user has access to specified user
		if ($actor->isNot(Auth::user()) && !Auth::user()->hasAccessTo($actor)) {
			return new Response([[
				                     "code"    => "shares.forbidden",
				                     "message" => "User cannot access shares of this actor",
			                     ]],
			                    403);
		}

		// We're good, list and return
		return new Response([
			                    "sharings" => $actor->sharings,
			                    "sharers"  => $actor->additional_accesses,
		                    ]);
	}

	public function store(StoreShareRequest $request, Actor $actor): Response {
		// We know the user has the proper capability and the request user property match a valid user.
		$share_with = $request->validated()["actor"];

		// A user cannot share with itself
		if ($share_with === $actor->id) {
			return new Response([[
				                     "code"    => "shares.not-allowed",
				                     "message" => "User cannot share with itself",
			                     ]],
			                    403);
		}

		// We also know from the request that the current user is allowed to share the target user children
		// Make sure the user is not already sharing with the specified one
		if ($actor->sharings->contains($share_with)) {
			// Cannot share again
			return new Response([[
				                     "code"    => "shares.already-shared",
				                     "message" => "Already sharing with this actor",
			                     ]],
			                    403);
		}

		// Everything looks fine, do the share
		$actor->sharings()->attach($share_with);

		return new Response(Actor::query()->find($share_with), 201);
	}

	public function destroy(DestroyShareRequest $request, Actor $actor): Response {
		$shared_with = $request->validated()["actor"];

		// Does a sharing from user to shared_with exists ?
		if (!$actor->sharings->contains($shared_with)) {
			return new Response([
				                    "code"    => "shares.do-not-exist",
				                    "message" => "Share do not exist",
			                    ],
			                    403);
		}

		// Relation exist, remove it.
		$actor->sharings()->detach($shared_with);

		return new Response([]);
	}
}
