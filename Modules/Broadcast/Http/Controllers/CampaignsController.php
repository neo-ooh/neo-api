<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Controllers\Controller;
use Neo\Models\Utils\ActorsGetter;
use Neo\Modules\Broadcast\Enums\ScheduleStatus;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\DestroyCampaignRequest;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\ListCampaignsByIdsRequest;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\ListCampaignsRequest;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\RefreshCampaignPerformancesRequest;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\ShowCampaignRequest;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\StoreCampaignRequest;
use Neo\Modules\Broadcast\Http\Requests\Campaigns\UpdateCampaignRequest;
use Neo\Modules\Broadcast\Jobs\Performances\FetchCampaignsPerformancesJob;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Properties\Models\Product;

/**
 * @phpstan-type CampaignLocation array{location_id: int, format_id: int, product_id: int|null}
 */
class CampaignsController extends Controller {
	/**
	 * @param ListCampaignsRequest $request
	 *
	 * @return Response
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function index(ListCampaignsRequest $request): Response {
		if ($request->input("parent_id")) {
			$getter = ActorsGetter::from($request->input("parent_id"))
			                      ->selectFocus();
			if ($request->input("recursive", false)) {
				$getter->selectChildren(recursive: true);
			}

			$actorIds = $getter->getSelection();
		} else {
			$actorIds = Auth::user()?->getAccessibleActors(shallow: !$request->input("recursive", true), ids: true);
		}

		/** @var Collection<Campaign> $campaigns */
		$campaigns = Campaign::query()->whereIn("parent_id", $actorIds)->get();

		if ($request->has("layout_id")) {
			$campaigns->load("layouts");
			$campaigns = $campaigns->filter(fn(Campaign $campaign) => $campaign->layouts->pluck("id")
			                                                                            ->contains($request->input("layout_id")));
		}

		$relations = $request->input("with", []);

		// If users wants to load the status relation, we make sure additional relations needed to compute a campaign status are also loaded, to prevent too many queries
		if (in_array("status", $relations, true)) {
			$campaigns->load(["schedules", "schedules.reviews"]);
		}

		return new Response($campaigns->loadPublicRelations()->values()->all());
	}

	public function byIds(ListCampaignsByIdsRequest $request) {
		$campaigns = Campaign::withTrashed()->findMany($request->input("ids"));

		return new Response($campaigns->loadPublicRelations());
	}

	/**
	 * @param StoreCampaignRequest $request
	 *
	 * @return Response
	 * @throws Exception
	 */
	public function store(StoreCampaignRequest $request): Response {
		$campaign = new Campaign();

		$campaign->creator_id     = Auth::id();
		$campaign->parent_id      = $request->input("parent_id");
		$campaign->name           = $request->input("name");
		$campaign->start_date     = $request->input("start_date");
		$campaign->start_time     = $request->input("start_time");
		$campaign->end_date       = $request->input("end_date");
		$campaign->end_time       = $request->input("end_time");
		$campaign->broadcast_days = $request->input("broadcast_days");
		$campaign->flight_id      = $request->input("flight_id");

		$campaign->occurrences_in_loop            = $request->input("occurrences_in_loop");
		$campaign->priority                       = $request->input("priority");
		$campaign->static_duration_override       = $request->input("static_duration_override");
		$campaign->dynamic_duration_override      = $request->input("dynamic_duration_override");
		$campaign->default_schedule_duration_days = $request->input("default_schedule_duration_days");

		// List all the locations given
		/** @var \Illuminate\Support\Collection<CampaignLocation> $locations */
		$locations = collect($request->input("locations", []))->map(fn($entry) => ([
			"location_id" => $entry["location_id"],
			"format_id"   => $entry["format_id"],
			"product_id"  => null,
		]));

		$productIds = collect($request->input("products", []));
		$products   = Product::query()->findMany($productIds);

		// We create the campaign and attach its location in a transaction as we want to prevent the campaign creation if there is a problem with the locations
		try {
			DB::beginTransaction();
			$campaign->save();

			// Set the campaign locations
			$campaign->locations()
			         ->sync($locations->mapWithKeys(fn(array $locationDefinition) => [$locationDefinition["location_id"] => [
				         "format_id"  => $locationDefinition["format_id"],
				         "product_id" => $locationDefinition["product_id"],
			         ]])->all());

			$campaign->products()->attach($products->pluck("id"));

			DB::commit();
		} catch (Exception $e) {
			DB::rollBack();

			throw $e;
		}

		if (Gate::allows(Capability::campaigns_tags->value)) {
			$campaign->broadcast_tags()->sync($request->input("tags"));
		}

		// Replicate the campaign in the appropriate broadcaster
		$campaign->promote();

		return new Response($campaign->loadPublicRelations(), 201);
	}

	/**
	 * @param ShowCampaignRequest $request
	 * @param Campaign            $campaign
	 *
	 * @return Response
	 */
	public function show(ShowCampaignRequest $request, Campaign $campaign): Response {
		return new Response($campaign->loadPublicRelations());
	}

	/**
	 * @param UpdateCampaignRequest $request
	 * @param Campaign              $campaign
	 *
	 * @return Response
	 */
	public function update(UpdateCampaignRequest $request, Campaign $campaign): Response {
		$campaign->parent_id      = $request->input("parent_id");
		$campaign->name           = $request->input("name");
		$campaign->start_date     = $request->input("start_date");
		$campaign->start_time     = $request->input("start_time");
		$campaign->end_date       = $request->input("end_date");
		$campaign->end_time       = $request->input("end_time");
		$campaign->broadcast_days = $request->input("broadcast_days");

		if (Gate::allows(Capability::contracts_edit->value)) {
			$campaign->flight_id = $request->input("flight_id");
		}

		$campaign->occurrences_in_loop            = $request->input("occurrences_in_loop");
		$campaign->priority                       = $request->input("priority");
		$campaign->static_duration_override       = $request->input("static_duration_override");
		$campaign->dynamic_duration_override      = $request->input("dynamic_duration_override");
		$campaign->default_schedule_duration_days = $request->input("default_schedule_duration_days");

		$campaign->save();

		if (Gate::allows(Capability::campaigns_tags->value) && $request->has("tags")) {
			$campaign->broadcast_tags()->sync($request->input("tags"));
		}

		$campaign->refresh();

		// We need to validate all the campaign's schedules' dates, times and days that haven't finished playing yet.
		$schedules = $campaign->schedules()->get();

		/** @var Schedule $schedule */
		foreach ($schedules as $schedule) {
			// Ignore schedules that will not play
			if ($schedule->status === ScheduleStatus::Expired || $schedule->status === ScheduleStatus::Rejected || $schedule->status === ScheduleStatus::Trashed) {
				continue;
			}

			// Dates
			$schedule->start_date = $schedule->start_date->isBetween($campaign->start_date, $campaign->end_date)
				? $schedule->start_date
				: $campaign->start_date->copy();
			$schedule->end_date   = $schedule->end_date->isBetween($campaign->start_date, $campaign->end_date)
				? $schedule->end_date
				: $campaign->end_date->copy();

			// Times
			$schedule->start_time = $schedule->start_time->isBetween($campaign->start_time, $campaign->end_time)
				? $schedule->start_time
				: $campaign->start_time->copy();
			$schedule->end_time   = $schedule->end_time->isBetween($campaign->start_time, $campaign->end_time)
				? $schedule->end_time
				: $campaign->end_time->copy();

			// Weekdays
			$schedule->broadcast_days &= $campaign->broadcast_days;

			$schedule->save();
		}

		$campaign->promote();

		return new Response($campaign->loadPublicRelations());
	}

	public function refreshPerformances(RefreshCampaignPerformancesRequest $request, Campaign $campaign) {
		FetchCampaignsPerformancesJob::dispatchSync(null, null, $campaign->getKey());

		return new Response(["ok"]);
	}

	public function destroy(DestroyCampaignRequest $request, Campaign $campaign): Response {
		$campaign->delete();

		return new Response($campaign);
	}
}
