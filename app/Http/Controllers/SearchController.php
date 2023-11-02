<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SearchController.php
 */

namespace Neo\Http\Controllers;

use Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Neo\Http\Requests\Search\SearchRequest;
use Neo\Http\Resources\SearchResult;
use Neo\Models\Actor;
use Neo\Models\Utils\ActorsGetter;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Library;

class SearchController extends Controller {
	public function search(SearchRequest $request) {
		// Search each selected resources and aggregate results
		$resources = $request->input("resources", []);
		$query     = $request->input("query", "");
		/** @var Collection<SearchResult> $results */
		$results = collect();

		foreach ($resources as $resource) {
			switch ($resource) {
				case "actors":
					$results->push(...Actor::search($query));
					break;
				case "campaigns":
					$results->push(...Campaign::search($query));
					break;
				case "libraries":
					$results->push(...Library::search($query));
					break;
			}
		}

		if ($request->input("hierarchy", false)) {
			// We want to load the parent hierarchy of all the results we found, and we make sure to not load an actor that is already part of the search results.
			$parentIds    = $results->pluck("parent_id")->unique();
			$actorIds     = $results->filter(fn(SearchResult $result) => $result->type === "actor")->pluck("id");
			$allParentIds = DB::table(ActorsGetter::CLOSURES_TABLE)
			                  ->whereIn("descendant_id", $parentIds)
			                  ->whereNotIn("descendant_id", $actorIds)
			                  ->pluck("ancestor_id");

			// Cross-match with accessible actors
			$accessibleActors = Auth::user()->getAccessibleActors(ids: true);
			$allParentIds     = $allParentIds->intersect($accessibleActors);

			$results->push(
				...Actor::query()
				        ->whereIn("id", $allParentIds)
				        ->select(["id", "name", "is_group"])
				        ->get()
				        ->map(fn(Actor $actor) => new SearchResult(
					        id       : $actor->getKey(),
					        type     : "actor",
					        subtype  : $actor->type->value,
					        label    : $actor->name,
					        parent_id: $actor->parent_id,
					        model    : $actor,
				        ))
			);
		}

		return new Response($results);
	}
}
